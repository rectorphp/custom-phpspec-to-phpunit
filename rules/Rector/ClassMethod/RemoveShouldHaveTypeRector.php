<?php

declare(strict_types=1);

namespace Rector\PhpSpecToPHPUnit\Rector\ClassMethod;

use PhpParser\Node;
use PhpParser\Node\Expr\MethodCall;
use PhpParser\Node\Stmt\Class_;
use PhpParser\Node\Stmt\ClassMethod;
use PhpParser\Node\Stmt\Expression;
use Rector\Core\Rector\AbstractRector;
use Rector\PhpSpecToPHPUnit\Enum\PhpSpecMethodName;
use Rector\PhpSpecToPHPUnit\NodeAnalyzer\PhpSpecBehaviorNodeDetector;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;

/**
 * @see \Rector\PhpSpecToPHPUnit\Tests\Rector\ClassMethod\RemoveShouldHaveTypeRector\RemoveShouldHaveTypeRectorTest
 */
final class RemoveShouldHaveTypeRector extends AbstractRector
{
    public function __construct(
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
    public function refactor(Node $node): ?Class_
    {
        if (! $this->phpSpecBehaviorNodeDetector->isInPhpSpecBehavior($node)) {
            return null;
        }

        $hasChanged = false;

        foreach ($node->stmts as $key => $classStmt) {
            if (! $classStmt instanceof ClassMethod) {
                continue;
            }

            if (! $classStmt->isPublic()) {
                continue;
            }

            // special case, @see https://johannespichler.com/writing-custom-phpspec-matchers/
            if ($this->isNames($classStmt, PhpSpecMethodName::RESERVED_CLASS_METHOD_NAMES)) {
                continue;
            }

            if (! $this->hasOnlyStmtWithShouldHaveTypeMethodCall($classStmt)) {
                continue;
            }

            // remove method as no point
            unset($node->stmts[$key]);
            $hasChanged = true;
        }

        if ($hasChanged) {
            return $node;
        }

        return null;
    }

    public function getRuleDefinition(): RuleDefinition
    {
        return new RuleDefinition('Remove shouldHaveType() check as pointless in time of PHP 7.0 types', [
            new CodeSample(
                <<<'CODE_SAMPLE'
use PhpSpec\ObjectBehavior;

class RenameMethodTest extends ObjectBehavior
{
    public function is_shoud_have_type()
    {
        $this->shouldHaveType(SomeType::class);
    }
}
CODE_SAMPLE
                ,
                <<<'CODE_SAMPLE'
use PhpSpec\ObjectBehavior;

class RenameMethodTest extends ObjectBehavior
{
}
CODE_SAMPLE
            ),

        ]);
    }

    private function hasOnlyStmtWithShouldHaveTypeMethodCall(ClassMethod $classMethod): bool
    {
        if (count((array) $classMethod->stmts) !== 1) {
            return false;
        }

        $onlyStmt = $classMethod->stmts[0] ?? null;
        if (! $onlyStmt instanceof Expression) {
            return false;
        }

        if (! $onlyStmt->expr instanceof MethodCall) {
            return false;
        }

        $methodCall = $onlyStmt->expr;
        return $this->isName($methodCall->name, PhpSpecMethodName::SHOULD_HAVE_TYPE);
    }
}
