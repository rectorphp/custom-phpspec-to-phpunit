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
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;

/**
 * @see \Rector\PhpSpecToPHPUnit\Tests\Rector\Class_\AddMockPropertiesRector\AddMockPropertiesRectorTest
 */
final class AddMockPropertiesRector extends AbstractRector
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

        $classMocks = $this->phpSpecMockCollector->resolveClassMocksFromParam($node);

        dump($classMocks);
        die;

        $className = $this->getName($node);
        if (! is_string($className)) {
            return null;
        }

        foreach ($classMocks as $name => $methods) {
            if (count($methods) <= 1) {
                continue;
            }

            // non-ctor used mocks are probably local only
            if (! in_array('let', $methods, true)) {
                continue;
            }

            $this->phpSpecMockCollector->addPropertyMock($className, $name);

            $variableType = $this->phpSpecMockCollector->getTypeForClassAndVariable($node, $name);
            $unionType = new UnionType([new ObjectType($variableType), new ObjectType(MockObject::class)]);

            // add property
            $property = $this->nodeFactory->createPrivatePropertyFromNameAndType($name, $unionType);
            $node->stmts = array_merge([$property], $node->stmts);
        }

        return null;
    }

    public function getRuleDefinition(): RuleDefinition
    {
        return new RuleDefinition('wip', []);
    }
}
