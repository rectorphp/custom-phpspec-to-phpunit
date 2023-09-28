<?php

declare(strict_types=1);

namespace Rector\PhpSpecToPHPUnit\Rector\ClassMethod;

use PhpParser\Node;
use PhpParser\Node\Identifier;
use PhpParser\Node\Stmt;
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
final class PhpSpecMethodToPHPUnitMethodRector extends AbstractRector
{
    public function __construct(
        private readonly PHPUnitTypeDeclarationDecorator $phpUnitTypeDeclarationDecorator,
        private readonly PhpSpecRenaming $phpSpecRenaming,
        private readonly VisibilityManipulator $visibilityManipulator,
        private readonly PhpSpecBehaviorNodeDetector $phpSpecBehaviorNodeDetector,
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

        if ($this->isName($node->name, 'letGo')) {
            $node->name = new Identifier('tearDown');
            $this->visibilityManipulator->makeProtected($node);

            $this->phpUnitTypeDeclarationDecorator->decorate($node);

            return $node;
        }

        if ($this->isName($node->name, 'let')) {
            $node->name = new Identifier(MethodName::SET_UP);
            $this->visibilityManipulator->makeProtected($node);
            $this->phpUnitTypeDeclarationDecorator->decorate($node);

            return $node;
        }

        if ($node->isPublic()) {
            return $this->processTestMethod($node);
        }

        return null;
    }

    public function getRuleDefinition(): RuleDefinition
    {
        return new RuleDefinition('wip', []);
    }

    private function processTestMethod(ClassMethod $classMethod): ?ClassMethod
    {
        // special case, @see https://johannespichler.com/writing-custom-phpspec-matchers/
        if ($this->isName($classMethod, 'getMatchers')) {
            return null;
        }

        // change name to phpunit test case format
        $this->phpSpecRenaming->renameMethod($classMethod);

        $hasChanged = false;

        // reorder instantiation + expected exception
        $previousStmt = null;
        foreach ((array) $classMethod->stmts as $key => $stmt) {
            // @todo why print?
            $printedStmtContent = $this->print($stmt);

            if (\str_contains((string) $printedStmtContent, 'duringInstantiation') && $previousStmt instanceof Stmt) {
                $printedPreviousStmt = $this->print($previousStmt);
                if (\str_contains((string) $printedPreviousStmt, 'beConstructedThrough')) {
                    $classMethod->stmts[$key - 1] = $stmt;
                    $classMethod->stmts[$key] = $previousStmt;
                }
            }

            $previousStmt = $stmt;
            $hasChanged = true;
        }

        if ($hasChanged) {
            return $classMethod;
        }

        return null;
    }
}
