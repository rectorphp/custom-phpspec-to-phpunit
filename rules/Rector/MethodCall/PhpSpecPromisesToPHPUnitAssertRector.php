<?php

declare(strict_types=1);

namespace Rector\PhpSpecToPHPUnit\Rector\MethodCall;

use PhpParser\Node;
use PhpParser\Node\Arg;
use PhpParser\Node\Expr\ArrayDimFetch;
use PhpParser\Node\Expr\Assign;
use PhpParser\Node\Expr\Clone_;
use PhpParser\Node\Expr\FuncCall;
use PhpParser\Node\Expr\MethodCall;
use PhpParser\Node\Expr\PropertyFetch;
use PhpParser\Node\Expr\Variable;
use PhpParser\Node\Identifier;
use PhpParser\Node\Scalar\String_;
use PhpParser\Node\Stmt\Class_;
use Rector\Core\Exception\ShouldNotHappenException;
use Rector\Core\Rector\AbstractRector;
use Rector\PhpSpecToPHPUnit\MatchersManipulator;
use Rector\PhpSpecToPHPUnit\Naming\PhpSpecRenaming;
use Rector\PhpSpecToPHPUnit\NodeAnalyzer\PhpSpecBehaviorNodeDetector;
use Rector\PhpSpecToPHPUnit\NodeFactory\AssertMethodCallFactory;
use Rector\PhpSpecToPHPUnit\NodeFactory\BeConstructedWithAssignFactory;
use Rector\PhpSpecToPHPUnit\NodeFactory\DuringMethodCallFactory;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;

/**
 * @see \Rector\PhpSpecToPHPUnit\Tests\Rector\Variable\PhpSpecToPHPUnitRector\PhpSpecToPHPUnitRectorTest
 */
final class PhpSpecPromisesToPHPUnitAssertRector extends AbstractRector
{
    /**
     * @changelog https://github.com/phpspec/phpspec/blob/master/src/PhpSpec/Wrapper/Subject.php
     * ↓
     * @changelog https://phpunit.readthedocs.io/en/8.0/assertions.html
     * @var array<string, string[]>
     */
    private const NEW_METHOD_TO_OLD_METHODS = [
        'assertInstanceOf' => ['shouldBeAnInstanceOf', 'shouldHaveType', 'shouldReturnAnInstanceOf'],
        'assertSame' => ['shouldBe', 'shouldReturn'],
        'assertNotSame' => ['shouldNotBe', 'shouldNotReturn'],
        'assertCount' => ['shouldHaveCount'],
        'assertEquals' => ['shouldBeEqualTo', 'shouldEqual'],
        'assertNotEquals' => ['shouldNotBeEqualTo'],
        'assertContains' => ['shouldContain'],
        'assertNotContains' => ['shouldNotContain'],
        // types
        'assertIsIterable' => ['shouldBeArray'],
        'assertIsNotIterable' => ['shouldNotBeArray'],
        'assertIsString' => ['shouldBeString'],
        'assertIsNotString' => ['shouldNotBeString'],
        'assertIsBool' => ['shouldBeBool', 'shouldBeBoolean'],
        'assertIsNotBool' => ['shouldNotBeBool', 'shouldNotBeBoolean'],
        'assertIsCallable' => ['shouldBeCallable'],
        'assertIsNotCallable' => ['shouldNotBeCallable'],
        'assertIsFloat' => ['shouldBeDouble', 'shouldBeFloat'],
        'assertIsNotFloat' => ['shouldNotBeDouble', 'shouldNotBeFloat'],
        'assertIsInt' => ['shouldBeInt', 'shouldBeInteger'],
        'assertIsNotInt' => ['shouldNotBeInt', 'shouldNotBeInteger'],
        'assertIsNull' => ['shouldBeNull'],
        'assertIsNotNull' => ['shouldNotBeNull'],
        'assertIsNumeric' => ['shouldBeNumeric'],
        'assertIsNotNumeric' => ['shouldNotBeNumeric'],
        'assertIsObject' => ['shouldBeObject'],
        'assertIsNotObject' => ['shouldNotBeObject'],
        'assertIsResource' => ['shouldBeResource'],
        'assertIsNotResource' => ['shouldNotBeResource'],
        'assertIsScalar' => ['shouldBeScalar'],
        'assertIsNotScalar' => ['shouldNotBeScalar'],
        'assertNan' => ['shouldBeNan'],
        'assertFinite' => ['shouldBeFinite', 'shouldNotBeFinite'],
        'assertInfinite' => ['shouldBeInfinite', 'shouldNotBeInfinite'],
    ];

    /**
     * @var string
     */
    private const THIS = 'this';

    private ?string $testedClass = null;

    private bool $isPrepared = false;

    /**
     * @var string[]
     */
    private array $matchersKeys = [];

    private ?PropertyFetch $testedObjectPropertyFetch = null;

    public function __construct(
        private readonly MatchersManipulator $matchersManipulator,
        private readonly PhpSpecRenaming $phpSpecRenaming,
        private readonly AssertMethodCallFactory $assertMethodCallFactory,
        private readonly BeConstructedWithAssignFactory $beConstructedWithAssignFactory,
        private readonly DuringMethodCallFactory $duringMethodCallFactory,
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
        $this->matchersKeys = [];

        $class = $node;

        $this->traverseNodesWithCallable($node->stmts, function (\PhpParser\Node $node) use ($class) {
            if (! $node instanceof MethodCall) {
                return null;
            }

            if ($this->isName($node->name, 'getWrappedObject')) {
                return $node->var;
            }

            if ($this->isName($node->name, 'during')) {
                return $this->duringMethodCallFactory->create($node, $this->getTestedObjectPropertyFetch());
            }

            if ($this->isName($node->name, 'duringInstantiation')) {
                return $this->processDuringInstantiation($node);
            }

            // skip reserved names
            $methodName = $this->getName($node->name);
            if (! is_string($methodName)) {
                return null;
            }

            if ($this->isNames($node->name, ['getMatchers', 'expectException']) || str_starts_with(
                $methodName,
                'assert'
            )) {
                return null;
            }

            $this->prepareMethodCall($class);

            if (str_starts_with($methodName, 'beConstructed')) {
                return $this->beConstructedWithAssignFactory->create(
                    $node,
                    $this->getTestedClass(),
                    $this->getTestedObjectPropertyFetch()
                );
            }

            $nodesToReturn = $this->processMatchersKeys($node);

            $args = $node->args;
            foreach (self::NEW_METHOD_TO_OLD_METHODS as $newMethod => $oldMethods) {
                if (! $this->isNames($node->name, $oldMethods)) {
                    continue;
                }

                return $this->assertMethodCallFactory->createAssertMethod(
                    $newMethod,
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

            $classMethod = $class->getMethod($methodName);

            // it's a local method call, skip
            if ($classMethod instanceof Node\Stmt\ClassMethod) {
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
        return new RuleDefinition('wip', []);
    }

    private function processDuringInstantiation(MethodCall $methodCall): MethodCall
    {
        /** @var MethodCall $parentMethodCall */
        $parentMethodCall = $methodCall->var;
        $parentMethodCall->name = new Identifier('expectException');

        return $parentMethodCall;
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

        $this->matchersKeys = $this->matchersManipulator->resolveMatcherNamesFromClass($class);
        $this->testedClass = $this->phpSpecRenaming->resolveTestedClass($class);
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

    /**
     * @changelog https://johannespichler.com/writing-custom-phpspec-matchers/
     * @return Node\Stmt[]|null
     */
    private function processMatchersKeys(MethodCall $methodCall): ?array
    {
        foreach ($this->matchersKeys as $matcherKey) {
            if (! $this->isName($methodCall->name, 'should' . ucfirst($matcherKey))) {
                continue;
            }

            if (! $methodCall->var instanceof MethodCall) {
                continue;
            }

            // 1. assign callable to variable
            $thisGetMatchers = $this->nodeFactory->createMethodCall(self::THIS, 'getMatchers');
            $arrayDimFetch = new ArrayDimFetch($thisGetMatchers, new String_($matcherKey));
            $matcherCallableVariable = new Variable('matcherCallable');
            $assign = new Assign($matcherCallableVariable, $arrayDimFetch);

            // 2. call it on result
            $funcCall = new FuncCall($matcherCallableVariable);
            $funcCall->args = $methodCall->args;

            $methodCall->name = $methodCall->var->name;
            $methodCall->var = $this->getTestedObjectPropertyFetch();
            $methodCall->args = [];
            $funcCall->args[] = new Arg($methodCall);

            return [new Node\Stmt\Expression($assign), new Node\Stmt\Expression($funcCall)];
        }

        return null;
    }

    private function shouldSkip(MethodCall $methodCall): bool
    {
        if (! $methodCall->var instanceof Variable) {
            return true;
        }

        if (! $this->nodeNameResolver->isName($methodCall->var, self::THIS)) {
            return true;
        }

        // skip "createMock" method
        return $this->isName($methodCall->name, 'createMock');
    }

    private function createTestedObjectPropertyFetch(Class_ $class): PropertyFetch
    {
        $propertyName = $this->phpSpecRenaming->resolveObjectPropertyName($class);
        return new PropertyFetch(new Variable(self::THIS), $propertyName);
    }
}
