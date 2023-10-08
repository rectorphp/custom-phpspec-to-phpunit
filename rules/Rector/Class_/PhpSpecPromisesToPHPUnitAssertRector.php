<?php

declare(strict_types=1);

namespace Rector\PhpSpecToPHPUnit\Rector\Class_;

use PhpParser\Node;
use PhpParser\Node\Expr\Clone_;
use PhpParser\Node\Expr\MethodCall;
use PhpParser\Node\Expr\PropertyFetch;
use PhpParser\Node\Expr\Variable;
use PhpParser\Node\Stmt\Class_;
use PhpParser\Node\Stmt\ClassMethod;
use Rector\Core\Exception\ShouldNotHappenException;
use Rector\Core\Rector\AbstractRector;
use Rector\PhpSpecToPHPUnit\Enum\PhpSpecMethodName;
use Rector\PhpSpecToPHPUnit\Enum\ProphecyPromisesToPHPUnitAssertMap;
use Rector\PhpSpecToPHPUnit\Naming\PhpSpecRenaming;
use Rector\PhpSpecToPHPUnit\NodeAnalyzer\PhpSpecBehaviorNodeDetector;
use Rector\PhpSpecToPHPUnit\NodeFactory\AssertMethodCallFactory;
use Rector\PhpSpecToPHPUnit\NodeFactory\BeConstructedWithAssignFactory;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;

/**
 * @see \Rector\PhpSpecToPHPUnit\Tests\Rector\Class_\PromisesToAssertsRector\PromisesToAssertsRectorTest
 */
final class PromisesToAssertsRector extends AbstractRector
{
    private ?string $testedClass = null;

    private bool $isPrepared = false;

    private ?PropertyFetch $testedObjectPropertyFetch = null;

    public function __construct(
        private readonly PhpSpecRenaming $phpSpecRenaming,
        private readonly AssertMethodCallFactory $assertMethodCallFactory,
        private readonly BeConstructedWithAssignFactory $beConstructedWithAssignFactory,
        private readonly PhpSpecBehaviorNodeDetector $phpSpecBehaviorNodeDetector
    ) {
    }

    /**
     * @return array<class-string<Node>>
     */
    public function getNodeTypes(): array
    {
        return [Class_::class];
    }

    /**
     * @param Class_ $node
     * @return \PhpParser\Node|Node[]|null
     */
    public function refactor(Node $node): Node|array|null
    {
        if (! $this->phpSpecBehaviorNodeDetector->isInPhpSpecBehavior($node)) {
            return null;
        }

        $this->isPrepared = false;

        $propertyName = $this->phpSpecRenaming->resolveTestedObjectPropertyName($node);

        $class = $node;

        $this->traverseNodesWithCallable($node, function (\PhpParser\Node $node) use (
            $class
        ): \PhpParser\Node|null {
            if (! $node instanceof MethodCall) {
                return null;
            }

            // unwrap getWrappedObject()
            if ($this->isName($node->name, PhpSpecMethodName::GET_WRAPPED_OBJECT)) {
                return $node->var;
            }

            if ($this->isNames(
                $node->name,
                [PhpSpecMethodName::DURING_INSTANTIATION, PhpSpecMethodName::DURING]
            )) {
                // handled in another rule
                return null;
            }

            // skip reserved names
            $methodName = $this->getName($node->name);
            if (! is_string($methodName)) {
                return null;
            }

            // handled elsewhere
            if ($this->isNames(
                $node->name,
                [PhpSpecMethodName::GET_MATCHERS, PhpSpecMethodName::EXPECT_EXCEPTION]
            ) || str_starts_with($methodName, 'assert')) {
                return null;
            }

            $this->prepareMethodCall($class);

            if (str_starts_with($methodName, PhpSpecMethodName::BE_CONSTRUCTED)) {
                return $this->beConstructedWithAssignFactory->create(
                    $node,
                    $this->getTestedClass(),
                    $this->getTestedObjectPropertyFetch()
                );
            }

            $args = $node->args;
            foreach (ProphecyPromisesToPHPUnitAssertMap::PROMISES_BY_ASSERT_METHOD as $assertMethod => $promiseMethods) {
                if (! $this->isNames($node->name, $promiseMethods)) {
                    continue;
                }

                return $this->assertMethodCallFactory->createAssertMethod(
                    $assertMethod,
                    $node->var,
                    $args[0]->value ?? null,
                    $this->getTestedObjectPropertyFetch()
                );
            }

            if ($this->shouldSkip($node)) {
                return null;
            }

            if ($this->isName($node->name, 'clone')) {
                return new Clone_($this->getTestedObjectPropertyFetch());
            }

            $methodName = $this->getName($node->name);
            if ($methodName === null) {
                return null;
            }

            // it's a local method call, skip
            if ($class->getMethod($methodName) instanceof ClassMethod) {
                return null;
            }

            // direct PHPUnit method calls, no need to call on property
            if (in_array($methodName, ['atLeastOnce', 'equalTo', 'isInstanceOf', 'isType'], true)) {
                return $node;
            }

            $node->var = $this->getTestedObjectPropertyFetch();

            return $node;
        });

        return $node;
    }

    public function getRuleDefinition(): RuleDefinition
    {
        return new RuleDefinition('Convert promises and object construction to new instances', [
            new CodeSample(
                <<<'CODE_SAMPLE'
use PhpSpec\ObjectBehavior;

class TestClassMethod extends ObjectBehavior
{
    public function let()
    {
        $this->beConstructedWith(5);
    }
}
CODE_SAMPLE
                ,
                <<<'CODE_SAMPLE'
use PhpSpec\ObjectBehavior;

class TestClassMethod extends ObjectBehavior
{
    public function let()
    {
        $testClassMethod = new \Rector\PhpSpecToPHPUnit\TestClassMethod(5);
    }
}
CODE_SAMPLE
            ),

        ]);
    }

    private function prepareMethodCall(Class_ $class): void
    {
        if ($this->isPrepared) {
            return;
        }

        $className = $this->getName($class);
        if (! is_string($className)) {
            return;
        }

        $this->testedClass = $this->phpSpecRenaming->resolveTestedClassName($class);
        $this->testedObjectPropertyFetch = $this->createTestedObjectPropertyFetch($class);

        $this->isPrepared = true;
    }

    private function getTestedObjectPropertyFetch(): PropertyFetch
    {
        if (! $this->testedObjectPropertyFetch instanceof PropertyFetch) {
            throw new ShouldNotHappenException();
        }

        return $this->testedObjectPropertyFetch;
    }

    private function getTestedClass(): string
    {
        if ($this->testedClass === null) {
            throw new ShouldNotHappenException();
        }

        return $this->testedClass;
    }

    private function shouldSkip(MethodCall $methodCall): bool
    {
        if (! $methodCall->var instanceof Variable) {
            return true;
        }

        if (! $this->nodeNameResolver->isName($methodCall->var, 'this')) {
            return true;
        }

        // skip "createMock" method
        return $this->isName($methodCall->name, 'createMock');
    }

    private function createTestedObjectPropertyFetch(Class_ $class): PropertyFetch
    {
        $propertyName = $this->phpSpecRenaming->resolveTestedObjectPropertyName($class);
        return new PropertyFetch(new Variable('this'), $propertyName);
    }
}
