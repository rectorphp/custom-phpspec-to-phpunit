<?php

declare(strict_types=1);

namespace Rector\PhpSpecToPHPUnit\Rector\ClassMethod;

use PhpParser\Node;
use PhpParser\Node\Stmt\ClassMethod;
use Rector\Core\PhpParser\Node\BetterNodeFinder;
use Rector\Core\Rector\AbstractRector;
use Rector\PhpSpecToPHPUnit\Naming\PhpSpecRenaming;
use Rector\PhpSpecToPHPUnit\NodeAnalyzer\PhpSpecBehaviorNodeDetector;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;

/**
 * @see \Rector\PhpSpecToPHPUnit\Tests\Rector\Variable\PhpSpecToPHPUnitRector\PhpSpecToPHPUnitRectorTest
 */
final class TestClassMethodRector extends AbstractRector
{
    public function __construct(
        private readonly PhpSpecRenaming $phpSpecRenaming,
        private readonly PhpSpecBehaviorNodeDetector $phpSpecBehaviorNodeDetector,
        private readonly BetterNodeFinder $betterNodeFinder,
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
        if (! $this->phpSpecBehaviorNodeDetector->isInPhpSpecBehavior($node)) {
            return null;
        }

        if (! $node->isPublic()) {
            return null;
        }

        // special case, @see https://johannespichler.com/writing-custom-phpspec-matchers/
        if ($this->isName($node, 'getMatchers')) {
            return null;
        }

        // @todo move here
        // change name to phpunit test case format
        $this->phpSpecRenaming->renameMethod($node);

        $hasChanged = false;

        // reorder instantiation + expected exception
        $previousStmt = null;
        foreach ((array) $node->stmts as $key => $stmt) {
            // has duringInstantiation() method?
            $hasDuringInstantiationMethodCall = $this->hasMethodCall($stmt, 'duringInstantiation');

            if ($hasDuringInstantiationMethodCall && $previousStmt instanceof Node\Stmt) {
                if ($this->hasMethodCall($previousStmt, 'beConstructedThrough')) {
                    $node->stmts[$key - 1] = $stmt;
                    $node->stmts[$key] = $previousStmt;
                }
            }

            $previousStmt = $stmt;
            $hasChanged = true;
        }

        if ($hasChanged) {
            return $node;
        }

        return null;
    }

    public function getRuleDefinition(): RuleDefinition
    {
        return new RuleDefinition('wip', []);
    }

    private function hasMethodCall(Node\Stmt $stmt, string $methodName): bool
    {
        return (bool) $this->betterNodeFinder->findFirst($stmt, function (Node $node) use ($methodName): bool {
            if (! $node instanceof Node\Expr\MethodCall) {
                return false;
            }

            return $this->isName($node->name, $methodName);
        });
    }
}
