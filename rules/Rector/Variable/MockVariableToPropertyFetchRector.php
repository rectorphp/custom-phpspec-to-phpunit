<?php

declare(strict_types=1);

namespace Rector\PhpSpecToPHPUnit\Rector\Variable;

use PhpParser\Node;
use PhpParser\Node\Expr\PropertyFetch;
use PhpParser\Node\Expr\Variable;
use PhpParser\Node\Stmt\Class_;
use Rector\Core\Rector\AbstractRector;
use Rector\PhpSpecToPHPUnit\NodeAnalyzer\PhpSpecBehaviorNodeDetector;
use Rector\PhpSpecToPHPUnit\PhpSpecMockCollector;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;

/**
 * @see \Rector\PhpSpecToPHPUnit\Tests\Rector\Variable\MockVariableToPropertyFetchRector\MockVariableToPropertyFetchRectorTest
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

        $hasChanged = false;

        $class = $node;
        $this->traverseNodesWithCallable($node->stmts, function (Node $node) use ($class, &$hasChanged) {
            if (! $node instanceof Variable) {
                return null;
            }

            if (! $this->phpSpecMockCollector->isVariableMockInProperty($class, $node)) {
                return null;
            }

            /** @var string $variableName */
            $variableName = $this->getName($node);

            $hasChanged = true;

            return new PropertyFetch(new Variable('this'), $variableName);
        });

        if ($hasChanged) {
            return $node;
        }

        return null;
    }

    public function getRuleDefinition(): RuleDefinition
    {
        return new RuleDefinition('Change local mock call to a property fetch mock call', [
new CodeSample(
<<<'CODE_SAMPLE'

 * $mock->call()
 * ↓
 * $this->mock->call()
CODE_SAMPLE
,
<<<'CODE_SAMPLE'

CODE_SAMPLE
)
        ]);
    }
}
