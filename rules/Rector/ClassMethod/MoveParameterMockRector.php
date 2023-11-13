<?php

declare(strict_types=1);

namespace Rector\PhpSpecToPHPUnit\Rector\ClassMethod;

use PhpParser\Node;
use PhpParser\Node\Arg;
use PhpParser\Node\Expr\Assign;
use PhpParser\Node\Expr\ClassConstFetch;
use PhpParser\Node\Expr\MethodCall;
use PhpParser\Node\Expr\PropertyFetch;
use PhpParser\Node\Expr\Variable;
use PhpParser\Node\Identifier;
use PhpParser\Node\Name\FullyQualified;
use PhpParser\Node\Stmt\Class_;
use PhpParser\Node\Stmt\ClassMethod;
use PhpParser\Node\Stmt\Expression;
use Rector\Core\Rector\AbstractRector;
use Rector\PhpSpecToPHPUnit\DocFactory;
use Rector\PhpSpecToPHPUnit\Enum\PhpSpecMethodName;
use Rector\PhpSpecToPHPUnit\NodeAnalyzer\LetClassMethodAnalyzer;
use Rector\PhpSpecToPHPUnit\ServiceMockResolver;
use Rector\PhpSpecToPHPUnit\ValueObject\ServiceMock;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;

/**
 * @see \Rector\PhpSpecToPHPUnit\Tests\Rector\ClassMethod\MoveParameterMockRector\MoveParameterMockRectorTest
 */
final class MoveParameterMockRector extends AbstractRector
{
    public function __construct(
        private readonly ServiceMockResolver $phpSpecMockCollector,
        private readonly LetClassMethodAnalyzer $letClassMethodAnalyzer,
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
    public function refactor(Node $node): ?Node
    {
        $hasChanged = false;

        $letDefinedVariables = $this->letClassMethodAnalyzer->resolveDefinedMockVariableNames($node);

        foreach ($node->getMethods() as $classMethod) {
            if ($this->shouldSkipClassMethod($classMethod)) {
                continue;
            }

            $serviceMocks = $this->phpSpecMockCollector->resolveServiceMocksFromClassMethodParams($classMethod);
            if ($serviceMocks === []) {
                continue;
            }

            // 1. remove params
            $classMethod->params = [];

            $newAssignExpressions = $this->createMockAssignExpressions($serviceMocks, $letDefinedVariables);

            // 2. add assigns
            $classMethod->stmts = array_merge($newAssignExpressions, (array) $classMethod->stmts);

            // 3. rename following variables
            $this->renameFollowingVariables($classMethod, $serviceMocks, $letDefinedVariables);
            $hasChanged = true;
        }

        if (! $hasChanged) {
            return null;
        }

        return $node;
    }

    public function getRuleDefinition(): RuleDefinition
    {
        return new RuleDefinition('Move parameter mocks to local mocks', [
            new CodeSample(
                <<<'CODE_SAMPLE'
use PhpSpec\ObjectBehavior;

final class AddMockProperty extends ObjectBehavior
{
    public function it_should_handle_stuff(SomeType $someType)
    {
    }
}
CODE_SAMPLE
                ,
                <<<'CODE_SAMPLE'
use PhpSpec\ObjectBehavior;

final class AddMockProperty extends ObjectBehavior
{
    public function it_should_handle_stuff()
    {
        $someTypeMock = $this->createMock(SomeType::class);
    }
}
CODE_SAMPLE
            ),
        ]);
    }

    private function createClassReferenceClassConstFetch(ServiceMock $serviceMock): ClassConstFetch
    {
        $classFullyQualified = new FullyQualified($serviceMock->getMockClassName());
        return new ClassConstFetch($classFullyQualified, 'class');
    }

    private function createMethodCallAssign(ServiceMock $serviceMock): Assign
    {
        $mockVariable = new Variable($serviceMock->getVariableName() . 'Mock');

        $createClassReferenceClassConstFetch = $this->createClassReferenceClassConstFetch($serviceMock);
        $createMockMethodCall = new MethodCall(new Variable('this'), new Identifier('createMock'), [
            new Arg($createClassReferenceClassConstFetch),
        ]);

        return new Assign($mockVariable, $createMockMethodCall);
    }

    /**
     * @param ServiceMock[] $serviceMocks
     * @param string[] $letDefinedVariables
     * @return array<Expression<Assign>>
     */
    private function createMockAssignExpressions(array $serviceMocks, array $letDefinedVariables): array
    {
        $newAssignExpressions = [];

        foreach ($serviceMocks as $serviceMock) {
            // skip is defined in the constructor
            if (in_array($serviceMock->getVariableName(), $letDefinedVariables, true)) {
                continue;
            }

            $assign = $this->createMethodCallAssign($serviceMock);
            $expression = new Expression($assign);

            // add docblock to help the IDE
            $assignDoc = DocFactory::createForMockAssign($serviceMock);
            $expression->setDocComment($assignDoc);

            $newAssignExpressions[] = $expression;
        }

        return $newAssignExpressions;
    }

    /**
     * @param string[] $letDefinedVariables
     * @param ServiceMock[] $serviceMocks
     */
    private function renameFollowingVariables(
        ClassMethod $classMethod,
        array $serviceMocks,
        array $letDefinedVariables
    ): void {
        $this->traverseNodesWithCallable((array) $classMethod->stmts, function (\PhpParser\Node $node) use (
            $serviceMocks,
            $letDefinedVariables
        ) {
            if (! $node instanceof Variable) {
                return null;
            }

            foreach ($serviceMocks as $serviceMock) {
                if (! $this->isName($node, $serviceMock->getVariableName())) {
                    continue;
                }

                $renamedName = $serviceMock->getVariableName() . 'Mock';
                if (in_array($serviceMock->getVariableName(), $letDefinedVariables, true)) {
                    return new PropertyFetch(new Variable('this'), $renamedName);
                }

                return new Variable($renamedName);
            }

            return null;
        });
    }

    private function shouldSkipClassMethod(ClassMethod $classMethod): bool
    {
        if (! $classMethod->isPublic()) {
            return true;
        }

        // handled somewhere else
        return $this->isNames($classMethod, [PhpSpecMethodName::LET_GO, PhpSpecMethodName::LET]);
    }
}
