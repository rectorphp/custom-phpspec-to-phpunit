<?php

declare(strict_types=1);

namespace Rector\PhpSpecToPHPUnit\Rector\ClassMethod;

use _HumbugBoxa23482e0566a\PhpParser\PrettyPrinter\Standard;
use PhpParser\Node;
use PhpParser\Node\Identifier;
use PhpParser\Node\Stmt\ClassMethod;
use Rector\Core\Rector\AbstractRector;
use Rector\Core\ValueObject\MethodName;
use Rector\PhpSpecToPHPUnit\Naming\PhpSpecRenaming;
use Rector\PhpSpecToPHPUnit\NodeAnalyzer\PhpSpecBehaviorNodeDetector;
use Rector\PhpSpecToPHPUnit\PHPUnitTypeDeclarationDecorator;
use Rector\Privatization\NodeManipulator\VisibilityManipulator;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;

/**
 * @see \Rector\PhpSpecToPHPUnit\Tests\Rector\Variable\PhpSpecToPHPUnitRector\PhpSpecToPHPUnitRectorTest
 */
final class TestClassMethodRector extends AbstractRector
{
    private \PhpParser\PrettyPrinter\Standard $printerStandard;

    public function __construct(
        private readonly PHPUnitTypeDeclarationDecorator $phpUnitTypeDeclarationDecorator,
        private readonly PhpSpecRenaming $phpSpecRenaming,
        private readonly VisibilityManipulator $visibilityManipulator,
        private readonly PhpSpecBehaviorNodeDetector $phpSpecBehaviorNodeDetector,
    ) {
        $this->printerStandard = new \PhpParser\PrettyPrinter\Standard();
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
            $printedStmtContent = $this->printerStandard->prettyPrint([$stmt]);

            if (\str_contains((string) $printedStmtContent, 'duringInstantiation') && $previousStmt instanceof Node\Stmt) {
                $printedPreviousStmt = $this->printerStandard->prettyPrint([$previousStmt]);
                if (\str_contains((string) $printedPreviousStmt, 'beConstructedThrough')) {
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
}
