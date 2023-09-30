<?php

declare(strict_types=1);

namespace Rector\PhpSpecToPHPUnit\Rector\Class_;

use PhpParser\Node;
use PhpParser\Node\Stmt\Class_;
use PHPStan\Type\ObjectType;
use PHPStan\Type\UnionType;
use PHPUnit\Framework\MockObject\MockObject;
use Rector\Core\Rector\AbstractRector;
use Rector\PhpSpecToPHPUnit\NodeAnalyzer\PhpSpecBehaviorNodeDetector;
use Rector\PhpSpecToPHPUnit\PhpSpecMockCollector;
use Rector\PhpSpecToPHPUnit\ValueObject\VariableMock;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;

/**
 * @see \Rector\PhpSpecToPHPUnit\Tests\Rector\Class_\MoveParameterMockToPropertyMockRector\MoveParameterMockToPropertyMockRectorTest
 */
final class MoveParameterMockToPropertyMockRector extends AbstractRector
{
    public function __construct(
        private readonly PhpSpecMockCollector $phpSpecMockCollector,
        private readonly PhpSpecBehaviorNodeDetector $phpSpecBehaviorNodeDetector,
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
        if (! $this->phpSpecBehaviorNodeDetector->isInPhpSpecBehavior($node)) {
            return null;
        }


        $serviceMocks = $this->phpSpecMockCollector->resolveVariableMocksFromClassMethodParams($node);
        if ($serviceMocks === []) {
            return null;
        }

        $newProperties = [];
        foreach ($serviceMocks as $variableMock) {
            $unionType = $this->createUnionType($variableMock);

            // add mock property
            $property = $this->nodeFactory->createPrivatePropertyFromNameAndType(
                $variableMock->getVariableName(),
                $unionType
            );

            $newProperties[] = $property;
        }

        if ($newProperties === []) {
            return null;
        }

        // cleanup mock parameters
        foreach ($node->getMethods() as $classMethod) {
            if (! $classMethod->isPublic() || $classMethod->name->toString() === 'let') {
                continue;
            }

            $classMethod->params = [];
        }

        $node->stmts = array_merge($newProperties, $node->stmts);

        return $node;
    }

    public function getRuleDefinition(): RuleDefinition
    {
        return new RuleDefinition('Move public class method parameter mocks to properties mocks', [
            new CodeSample(
                <<<'CODE_SAMPLE'
use PhpSpec\ObjectBehavior;

final class AddMockProperty extends ObjectBehavior
{
    public function let(SomeType $someType)
    {
    }
}
CODE_SAMPLE
                ,
                <<<'CODE_SAMPLE'
use PhpSpec\ObjectBehavior;

final class AddMockProperty extends ObjectBehavior
{
    private \Rector\PhpSpecToPHPUnit\Tests\Rector\Class_\AddMockPropertiesRector\Source\SomeType|\PHPUnit\Framework\MockObject\MockObject $someType;

    public function let(SomeType $someType)
    {
    }
}
CODE_SAMPLE
            ),
        ]);
    }

    private function createUnionType(VariableMock $variableMock): UnionType
    {
        $mockObjectType = new ObjectType(MockObject::class);
        $variableObjectType = new ObjectType($variableMock->getMockClassName());

        return new UnionType([$variableObjectType, $mockObjectType]);
    }
}
