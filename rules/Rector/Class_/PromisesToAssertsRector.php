<?php

declare(strict_types=1);

namespace Rector\PhpSpecToPHPUnit\Rector\Class_;

use PhpParser\Node;
use PhpParser\Node\Expr;
use PhpParser\Node\Expr\Assign;
use PhpParser\Node\Expr\Clone_;
use PhpParser\Node\Expr\MethodCall;
use PhpParser\Node\Expr\PropertyFetch;
use PhpParser\Node\Expr\Variable;
use PhpParser\Node\Stmt\Class_;
use PHPStan\Node\ClassMethod;
use Rector\PhpSpecToPHPUnit\Enum\PhpSpecMethodName;
use Rector\PhpSpecToPHPUnit\Enum\PHPUnitMethodName;
use Rector\PhpSpecToPHPUnit\Enum\ProphecyPromisesToPHPUnitAssertMap;
use Rector\PhpSpecToPHPUnit\Naming\PhpSpecRenaming;
use Rector\PhpSpecToPHPUnit\Naming\SystemMethodDetector;
use Rector\PhpSpecToPHPUnit\NodeFactory\AssertMethodCallFactory;
use Rector\PhpSpecToPHPUnit\NodeFactory\BeConstructedWithAssignFactory;
use Rector\Rector\AbstractRector;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;

/**
 * @see \Rector\PhpSpecToPHPUnit\Tests\Rector\Class_\PromisesToAssertsRector\PromisesToAssertsRectorTest
 */
final class PromisesToAssertsRector extends AbstractRector
{
    public function __construct(
        private readonly PhpSpecRenaming $phpSpecRenaming,
        private readonly AssertMethodCallFactory $assertMethodCallFactory,
        private readonly BeConstructedWithAssignFactory $beConstructedWithAssignFactory,
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
     */
    public function refactor(Node $node): Node|null
    {
        $hasChanged = false;

        $class = $node;
        $testedObject = $this->phpSpecRenaming->resolveTestedObject($class);

        $testedObjectPropertyFetch = $this->createTestedObjectPropertyFetch($class);

        $localMethodNames = $this->getLocalMethodNames($node);

        foreach ($node->getMethods() as $classMethod) {
            if (! $classMethod->isPublic()) {
                continue;
            }

            // handled elsewhere
            if ($this->isNames(
                $classMethod,
                [PhpSpecMethodName::LET, PhpSpecMethodName::LET_GO, PhpSpecMethodName::GET_MATCHERS]
            )) {
                continue;
            }

            $this->traverseNodesWithCallable($classMethod, function (Node $node) use (
                $class,
                $testedObjectPropertyFetch,
                $testedObject,
                &$hasChanged,
                $localMethodNames,
            ): null|Expr|Assign|MethodCall|Clone_ {
                if (! $node instanceof MethodCall) {
                    return null;
                }

                if ($this->isName($node->name, PHPUnitMethodName::ANY)) {
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

                // skip local method names
                if (in_array($methodName, $localMethodNames, true)) {
                    return null;
                }

                // handled elsewhere
                if ($this->isNames(
                    $node->name,
                    [PhpSpecMethodName::GET_MATCHERS, PhpSpecMethodName::EXPECT_EXCEPTION]
                ) || str_starts_with($methodName, 'assert')) {
                    return null;
                }

                if (str_starts_with($methodName, PhpSpecMethodName::BE_CONSTRUCTED)) {
                    $hasChanged = true;

                    return $this->beConstructedWithAssignFactory->create(
                        $node,
                        $testedObject->getClassName(),
                        $testedObjectPropertyFetch
                    );
                }

                $args = $node->args;
                foreach (ProphecyPromisesToPHPUnitAssertMap::PROMISES_BY_ASSERT_METHOD as $assertMethod => $promiseMethods) {
                    if (! $this->isNames($node->name, $promiseMethods)) {
                        continue;
                    }

                    $hasChanged = true;

                    return $this->assertMethodCallFactory->createAssertMethod(
                        $assertMethod,
                        $node->var,
                        $args[0]->value ?? null,
                        $testedObjectPropertyFetch
                    );
                }

                if ($this->shouldSkip($node)) {
                    return null;
                }

                if ($this->isName($node->name, PhpSpecMethodName::CLONE)) {
                    $hasChanged = true;
                    return new Clone_($testedObjectPropertyFetch);
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
                if (SystemMethodDetector::detect($methodName)) {
                    return $node;
                }

                $node->var = $testedObjectPropertyFetch;
                $hasChanged = true;

                return $node;
            });
        }

        if (! $hasChanged) {
            return null;
        }

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
    public function it_shoud_do()
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
    public function it_shoud_do()
    {
        $testClassMethod = new \Rector\PhpSpecToPHPUnit\TestClassMethod(5);
    }
}
CODE_SAMPLE
            ),

        ]);
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
        return $this->isName($methodCall->name, PHPUnitMethodName::CREATE_MOCK);
    }

    private function createTestedObjectPropertyFetch(Class_ $class): PropertyFetch
    {
        $testedObject = $this->phpSpecRenaming->resolveTestedObject($class);

        return new PropertyFetch(new Variable('this'), $testedObject->getPropertyName());
    }

    /**
     * @return string[]
     */
    private function getLocalMethodNames(Class_ $class): array
    {
        $localMethodNames = [];
        foreach ($class->getMethods() as $classMethod) {
            $localMethodNames[] = $this->getName($classMethod);
        }

        /** @var string[] $localMethodNames */
        return $localMethodNames;
    }
}
