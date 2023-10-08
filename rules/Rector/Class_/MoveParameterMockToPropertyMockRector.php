<?php

declare(strict_types=1);

namespace Rector\PhpSpecToPHPUnit\Rector\Class_;

use PhpParser\Node;
use PhpParser\Node\Stmt\Class_;
use PHPStan\Type\ObjectType;
use PHPUnit\Framework\MockObject\MockObject;
use Rector\Core\Rector\AbstractRector;
use Rector\PhpSpecToPHPUnit\DocFactory;
use Rector\PhpSpecToPHPUnit\NodeAnalyzer\PhpSpecBehaviorNodeDetector;
use Rector\PhpSpecToPHPUnit\PhpSpecMockCollector;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;

/**
 * @see \Rector\PhpSpecToPHPUnit\Tests\Rector\Class_\MoveParameterMockToPropertyMockRector\MoveParameterMockToPropertyMockRectorTest
 */
final class MoveParameterMockToPropertyMockRector extends AbstractRector
{
    /**
     * @readonly
     * @var \Rector\PhpSpecToPHPUnit\PhpSpecMockCollector
     */
    private $phpSpecMockCollector;
    /**
     * @readonly
     * @var \Rector\PhpSpecToPHPUnit\NodeAnalyzer\PhpSpecBehaviorNodeDetector
     */
    private $phpSpecBehaviorNodeDetector;
    public function __construct(PhpSpecMockCollector $phpSpecMockCollector, PhpSpecBehaviorNodeDetector $phpSpecBehaviorNodeDetector)
    {
        $this->phpSpecMockCollector = $phpSpecMockCollector;
        $this->phpSpecBehaviorNodeDetector = $phpSpecBehaviorNodeDetector;
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
            $property = $this->nodeFactory->createPrivatePropertyFromNameAndType(
                $variableMock->getVariableName(),
                new ObjectType(MockObject::class)
            );

            // add docblock to help the IDE
            $propertyDoc = DocFactory::createForProperty($variableMock);
            $property->setDocComment($propertyDoc);

            $newProperties[] = $property;
        }

        // cleanup mock parameters
        foreach ($node->getMethods() as $classMethod) {
            if (! $classMethod->isPublic()) {
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
}
