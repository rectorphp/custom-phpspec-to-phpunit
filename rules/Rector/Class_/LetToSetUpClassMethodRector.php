<?php

declare(strict_types=1);

namespace Rector\PhpSpecToPHPUnit\Rector\Class_;

use PhpParser\Node;
use PhpParser\Node\Arg;
use PhpParser\Node\Expr\Assign;
use PhpParser\Node\Expr\MethodCall;
use PhpParser\Node\Expr\New_;
use PhpParser\Node\Expr\PropertyFetch;
use PhpParser\Node\Expr\Variable;
use PhpParser\Node\Identifier;
use PhpParser\Node\Name\FullyQualified;
use PhpParser\Node\Param;
use PhpParser\Node\Stmt\Class_;
use PhpParser\Node\Stmt\ClassMethod;
use PhpParser\Node\Stmt\Expression;
use PhpParser\Node\Stmt\Property;
use Rector\PhpSpecToPHPUnit\Enum\PhpSpecMethodName;
use Rector\PhpSpecToPHPUnit\MockVariableReplacer;
use Rector\PhpSpecToPHPUnit\Naming\PhpSpecRenaming;
use Rector\PhpSpecToPHPUnit\Naming\PropertyNameResolver;
use Rector\PhpSpecToPHPUnit\NodeFactory\LetMockNodeFactory;
use Rector\PhpSpecToPHPUnit\NodeFinder\MethodCallFinder;
use Rector\PhpSpecToPHPUnit\ValueObject\TestedObject;
use Rector\Privatization\NodeManipulator\VisibilityManipulator;
use Rector\Rector\AbstractRector;
use Rector\ValueObject\MethodName;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;

/**
 * @see \Rector\PhpSpecToPHPUnit\Tests\Rector\Class_\LetToSetUpClassMethodRector\LetToSetUpClassMethodRectorTest
 */
final class LetToSetUpClassMethodRector extends AbstractRector
{
    /**
     * @readonly
     */
    private VisibilityManipulator $visibilityManipulator;
    /**
     * @readonly
     */
    private PhpSpecRenaming $phpSpecRenaming;
    /**
     * @readonly
     */
    private LetMockNodeFactory $letMockNodeFactory;
    /**
     * @readonly
     */
    private MockVariableReplacer $mockVariableReplacer;
    public function __construct(VisibilityManipulator $visibilityManipulator, PhpSpecRenaming $phpSpecRenaming, LetMockNodeFactory $letMockNodeFactory, MockVariableReplacer $mockVariableReplacer)
    {
        $this->visibilityManipulator = $visibilityManipulator;
        $this->phpSpecRenaming = $phpSpecRenaming;
        $this->letMockNodeFactory = $letMockNodeFactory;
        $this->mockVariableReplacer = $mockVariableReplacer;
    }

    /**
     * @return array<class-string<Node>>
     */
    public function getNodeTypes(): array
    {
        return [Class_::class];
    }

    public function getRuleDefinition(): RuleDefinition
    {
        return new RuleDefinition(
            'Change let() method to setUp() PHPUnit method, including property mock initialization',
            [
                new CodeSample(
                    <<<'CODE_SAMPLE'
use PhpSpec\ObjectBehavior;

final class SomeTypeSpec extends ObjectBehavior
{
    public function let(SomeDependency $someDependency)
    {
        $this->beConstructedWith($someDependency);
    }
}
CODE_SAMPLE
                    ,
                    <<<'CODE_SAMPLE'
use PhpSpec\ObjectBehavior;
use PHPUnit\Framework\MockObject\MockObject;

final class SomeTypeSpec extends ObjectBehavior
{
    private SomeType $someType;

    /**
     * @var MockObject<SomeDependency>
     */
    private MockObject $someDependencyMock;

    protected function setUp(): void
    {
        $this->someDependencyMock = $this->createMock(SomeDependency::class);
        $this->someType = new SomeType($this->someDependencyMock);
    }
}
CODE_SAMPLE
                ),

            ]
        );
    }

    /**
     * @param Class_ $node
     */
    public function refactor(Node $node): ?Node
    {
        $letClassMethod = $node->getMethod(PhpSpecMethodName::LET);
        if (! $letClassMethod instanceof ClassMethod) {
            return null;
        }

        $this->removeInstanceOfCheck($letClassMethod);

        $testedObject = $this->phpSpecRenaming->resolveTestedObject($node);

        $mockParams = $letClassMethod->getParams();

        $mockAssignExpressions = $this->letMockNodeFactory->createMockPropertyAssignExpressions($mockParams);

        $assignExpression = $this->createMockObjectAssign($testedObject, $mockParams);

        // update mock variables to properties references
        $mockPropertyNames = PropertyNameResolver::resolveFromPropertyAssigns($mockAssignExpressions);
        $this->mockVariableReplacer->replaceVariableMockByProperties($letClassMethod, $mockPropertyNames);

        // add tested object properties
        $newProperties = $this->createNewClassProperties($mockParams, $testedObject);

        $this->refactorToSetUpClassMethod($letClassMethod);

        $newLetStmts = $mockAssignExpressions;
        if (! MethodCallFinder::hasByName($letClassMethod, PhpSpecMethodName::BE_CONSTRUCTED_WITH)) {
            $newLetStmts[] = $assignExpression;
        } else {
            $this->changeBeConstructedWithToAnAssign($letClassMethod, $testedObject);
        }

        $letClassMethod->stmts = array_merge($newLetStmts, (array) $letClassMethod->stmts);

        $node->stmts = array_merge($newProperties, $node->stmts);

        return $node;
    }

    private function changeBeConstructedWithToAnAssign(ClassMethod $classMethod, TestedObject $testedObject): void
    {
        $this->traverseNodesWithCallable((array) $classMethod->stmts, function (Node $node) use (
            $testedObject
        ): ?Assign {
            if (! $node instanceof MethodCall) {
                return null;
            }

            if (! $this->isName($node->name, PhpSpecMethodName::BE_CONSTRUCTED_WITH)) {
                return null;
            }

            $newArgs = $this->normalizeMockVariablesToPropertyFetches(
                $node->getArgs(),
                $testedObject->getDefinedMockVariableNames()
            );

            $testedObjectFullyQualified = $testedObject->getTestedObjectFullyQualified();

            $new = new New_($testedObjectFullyQualified, $newArgs);
            $mockPropertyFetch = new PropertyFetch(new Variable('this'), new Identifier(
                $testedObject->getPropertyName()
            ));

            return new Assign($mockPropertyFetch, $new);
        });
    }

    private function createTestedObjectProperty(TestedObject $testedObject): Property
    {
        return $this->nodeFactory->createPrivatePropertyFromNameAndType(
            $testedObject->getPropertyName(),
            $testedObject->getTestedObjectType()
        );
    }

    /**
     * @param Param[] $params
     * @return Expression<Assign>
     */
    private function createMockObjectAssign(TestedObject $testedObject, array $params): Expression
    {
        $mockObjectPropertyFetch = new PropertyFetch(new Variable('this'), new Identifier(
            $testedObject->getPropertyName()
        ));

        $newArgs = $this->createTestedObjectNewArgs($params);

        $new = new New_(new FullyQualified($testedObject->getClassName()), $newArgs);
        $assign = new Assign($mockObjectPropertyFetch, $new);

        return new Expression($assign);
    }

    private function refactorToSetUpClassMethod(ClassMethod $letClassMethod): void
    {
        $letClassMethod->name = new Identifier(MethodName::SET_UP);
        $letClassMethod->returnType = new Identifier('void');
        $letClassMethod->params = [];

        $this->visibilityManipulator->makeProtected($letClassMethod);
    }

    /**
     * @param Param[] $params
     * @return Arg[]
     */
    private function createTestedObjectNewArgs(array $params): array
    {
        $newArgs = [];
        foreach ($params as $param) {
            $parameterName = $this->getName($param) . 'Mock';
            $mockProperty = new PropertyFetch(new Variable('this'), $parameterName);

            $newArgs[] = new Arg($mockProperty);
        }

        return $newArgs;
    }

    /**
     * @param Arg[] $args
     * @param string[] $mockVariableNames
     * @return Arg[]
     */
    private function normalizeMockVariablesToPropertyFetches(array $args, array $mockVariableNames): array
    {
        if ($mockVariableNames === []) {
            return $args;
        }

        foreach ($args as $arg) {
            if (! $arg->value instanceof Variable) {
                continue;
            }

            $variable = $arg->value;
            if (! $this->isNames($variable, $mockVariableNames)) {
                continue;
            }

            $variableName = $this->getName($variable);

            // rename mock variable defined in let to a property fetch
            $arg->value = new PropertyFetch(new Variable('this'), $variableName . 'Mock');
        }

        return $args;
    }

    /**
     * @param Param[] $mockParams
     * @return Property[]
     */
    private function createNewClassProperties(array $mockParams, TestedObject $testedObject): array
    {
        $mockProperties = $this->letMockNodeFactory->createMockProperties($mockParams);
        $testedObjectProperty = $this->createTestedObjectProperty($testedObject);

        return array_merge($mockProperties, [$testedObjectProperty]);
    }

    /**
     * Remove instanceof check as handled in native way by typed property
     */
    private function removeInstanceOfCheck(ClassMethod $letClassMethod): void
    {
        foreach ((array) $letClassMethod->stmts as $key => $stmt) {
            if (! $stmt instanceof Expression) {
                continue;
            }

            if (! $stmt->expr instanceof MethodCall) {
                continue;
            }

            if (! $this->isName($stmt->expr->name, PhpSpecMethodName::BE_AN_INSTANCE_OF)) {
                continue;
            }

            unset($letClassMethod->stmts[$key]);
        }
    }
}
