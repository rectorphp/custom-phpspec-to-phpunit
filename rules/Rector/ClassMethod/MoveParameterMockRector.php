<?php

declare(strict_types=1);

namespace Rector\PhpSpecToPHPUnit\Rector\ClassMethod;

use PhpParser\Node;
use PhpParser\Node\Arg;
use PhpParser\Node\Expr\Assign;
use PhpParser\Node\Expr\ClassConstFetch;
use PhpParser\Node\Expr\MethodCall;
use PhpParser\Node\Expr\Variable;
use PhpParser\Node\Identifier;
use PhpParser\Node\Name\FullyQualified;
use PhpParser\Node\Stmt\ClassMethod;
use PhpParser\Node\Stmt\Expression;
use Rector\Core\Rector\AbstractRector;
use Rector\PhpSpecToPHPUnit\DocFactory;
use Rector\PhpSpecToPHPUnit\PhpSpecMockCollector;
use Rector\PhpSpecToPHPUnit\ValueObject\ServiceMock;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;

/**
 * @see \Rector\PhpSpecToPHPUnit\Tests\Rector\ClassMethod\MoveParameterMockRector\MoveParameterMockRectorTest
 */
final class MoveParameterMockRector extends AbstractRector
{
    public function __construct(
        private readonly PhpSpecMockCollector $phpSpecMockCollector,
    ) {
    }

    /**
     * @return array<class-string<Node>>
     */
    public function getNodeTypes(): array
    {
        return [ClassMethod::class];
    }

    /**
     * @param ClassMethod $node
     */
    public function refactor(Node $node): ?Node
    {
        if (! $node->isPublic()) {
            return null;
        }

        $serviceMocks = $this->phpSpecMockCollector->resolveServiceMocksFromClassMethodParams($node);
        if ($serviceMocks === []) {
            return null;
        }

        // 1. remove params
        $node->params = [];

        $newAssignExpressions = $this->createMockAssignExpressions($serviceMocks);

        // 2. add assigns
        $node->stmts = array_merge($newAssignExpressions, (array) $node->stmts);

        // 3. rename following variables
        $this->traverseNodesWithCallable($node->stmts, function (\PhpParser\Node $node) use ($serviceMocks) {
            if (! $node instanceof Variable) {
                return null;
            }

            foreach ($serviceMocks as $serviceMock) {
                if (! $this->isName($node, $serviceMock->getVariableName())) {
                    continue;
                }

                return new Variable($serviceMock->getVariableName() . 'Mock');
            }

            return null;
        });

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
     * @return array<Expression<Assign>>
     */
    private function createMockAssignExpressions(array $serviceMocks): array
    {
        $newAssignExpressions = [];

        foreach ($serviceMocks as $serviceMock) {
            $assign = $this->createMethodCallAssign($serviceMock);

            $expression = new Expression($assign);

            // add docblock to help the IDE
            $assignDoc = DocFactory::createForMockAssign($serviceMock);
            $expression->setDocComment($assignDoc);

            $newAssignExpressions[] = $expression;
        }

        return $newAssignExpressions;
    }
}
