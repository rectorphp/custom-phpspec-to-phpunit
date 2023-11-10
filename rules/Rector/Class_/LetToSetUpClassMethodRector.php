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
use PHPStan\Type\ObjectType;
use Rector\Core\PhpParser\Node\BetterNodeFinder;
use Rector\Core\Rector\AbstractRector;
use Rector\Core\ValueObject\MethodName;
use Rector\PhpSpecToPHPUnit\Enum\PhpSpecMethodName;
use Rector\PhpSpecToPHPUnit\Naming\PhpSpecRenaming;
use Rector\PhpSpecToPHPUnit\NodeAnalyzer\LetClassMethodAnalyzer;
use Rector\PhpSpecToPHPUnit\NodeFactory\LetMockNodeFactory;
use Rector\PhpSpecToPHPUnit\ValueObject\TestedObject;
use Rector\Privatization\NodeManipulator\VisibilityManipulator;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;

/**
 * @see \Rector\PhpSpecToPHPUnit\Tests\Rector\Class_\LetToSetUpClassMethodRector\LetToSetUpClassMethodRectorTest
 */
final class LetToSetUpClassMethodRector extends AbstractRector
{
    public function __construct(
        private readonly VisibilityManipulator $visibilityManipulator,
        private readonly PhpSpecRenaming $phpSpecRenaming,
        private readonly BetterNodeFinder $betterNodeFinder,
        private readonly LetMockNodeFactory $letMockNodeFactory,
    ) {
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

        //        $definedMockVariableNames = $this->letClassMethodAnalyzer->resolveDefinedMockVariableNames($node);
        $testedObject = $this->phpSpecRenaming->resolveTestedObject($node);

        $mockParams = $letClassMethod->getParams();

        $mockProperties = $this->letMockNodeFactory->createMockProperties($mockParams);
        $mockAssignExpressions = $this->letMockNodeFactory->createMockPropertyAssignExpressions($mockParams);

        $mockObjectAssign = $this->createMockObjectAssign($testedObject, $mockParams);

        // add tested object properties
        $testedObjectProperty = $this->createTestedObjectProperty($testedObject);
        $newProperties = array_merge($mockProperties, [$testedObjectProperty]);

        $this->refactorToSetUpClassMethod($letClassMethod);

        $newLetStmts = $mockAssignExpressions;
        if (! $this->hasBeConstructedWithMethodCall($letClassMethod)) {
            $newLetStmts[] = $mockObjectAssign;
        } else {
            $this->changeBeConstructedWithToAnAssign($letClassMethod, $testedObject);
        }

        $letClassMethod->stmts = array_merge($newLetStmts, (array) $letClassMethod->stmts);

        $node->stmts = array_merge($newProperties, $node->stmts);

        return $node;
    }

    private function changeBeConstructedWithToAnAssign(
        ClassMethod $letClassMethod,
        TestedObject $testedObject
        //        ObjectType $testedObjectType,
        //        string $testedObjectPropertyName,
        //        array $definedMockVariableNames
    ): void {
        $this->traverseNodesWithCallable($letClassMethod, function (Node $node) use ($testedObject) {
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

    private function hasBeConstructedWithMethodCall(ClassMethod $letClassMethod): bool
    {
        return (bool) $this->betterNodeFinder->findFirst((array) $letClassMethod->stmts, function (Node $node) {
            if (! $node instanceof MethodCall) {
                return false;
            }

            return $this->isName($node->name, PhpSpecMethodName::BE_CONSTRUCTED_WITH);
        });
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
     * @param string[] $definedMockVariableNames
     * @return Arg[]
     */
    private function normalizeMockVariablesToPropertyFetches(array $args, array $definedMockVariableNames)
    {
        if ($definedMockVariableNames === []) {
            return $args;
        }

        foreach ($args as $arg) {
            if (! $arg->value instanceof Variable) {
                continue;
            }

            $variable = $arg->value;
            if (! $this->isNames($variable, $definedMockVariableNames)) {
                continue;
            }

            $variableName = $this->getName($variable);

            // rename mock variable defined in let to a property fetch
            $arg->value = new PropertyFetch(new Variable('this'), $variableName . 'Mock');
        }

        return $args;
    }
}
