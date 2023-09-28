<?php

declare(strict_types=1);

namespace Rector\PhpSpecToPHPUnit\Rector\Variable;

use PhpParser\Node;
use PhpParser\Node\Expr\PropertyFetch;
use PhpParser\Node\Expr\Variable;
use PHPStan\Analyser\Scope;
use Rector\Core\Rector\AbstractRector;
use Rector\NodeTypeResolver\Node\AttributeKey;
use Rector\PhpSpecToPHPUnit\NodeAnalyzer\PhpSpecBehaviorNodeDetector;
use Rector\PhpSpecToPHPUnit\PhpSpecMockCollector;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;

/**
 * $mock->call()
 * ↓
 * $this->mock->call()
 *
 * @see \Rector\PhpSpecToPHPUnit\Tests\Rector\Variable\PhpSpecToPHPUnitRector\PhpSpecToPHPUnitRectorTest
 */
final class MockVariableToPropertyFetchRector extends AbstractRector
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
        return [Variable::class];
    }

    /**
     * @param Variable $node
     */
    public function refactor(Node $node): ?Node
    {
        $scope = $node->getAttribute(AttributeKey::SCOPE);
        if (! $scope instanceof Scope) {
            return null;
        }

        if (! $this->phpSpecBehaviorNodeDetector->isInPhpSpecBehavior($class)) {
            return null;
        }

        if (! $this->phpSpecMockCollector->isVariableMockInProperty($class, $node)) {
            return null;
        }

        /** @var string $variableName */
        $variableName = $this->getName($node);

        return new PropertyFetch(new Variable('this'), $variableName);
    }

    public function getRuleDefinition(): RuleDefinition
    {
        return new RuleDefinition('wip', []);
    }
}
