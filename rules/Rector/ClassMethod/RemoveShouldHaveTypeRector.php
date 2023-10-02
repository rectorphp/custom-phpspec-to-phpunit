<?php

declare(strict_types=1);

namespace Rector\PhpSpecToPHPUnit\Rector\ClassMethod;

use PhpParser\Node;
use PhpParser\Node\Expr\MethodCall;
use PhpParser\Node\Stmt\ClassMethod;
use PhpParser\Node\Stmt\Expression;
use PhpParser\NodeTraverser;
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
        return [ClassMethod::class];
    }

    /**
     * @param ClassMethod $node
     */
    public function refactor(Node $node): ?int
    {
        if (! $this->phpSpecBehaviorNodeDetector->isInPhpSpecBehavior($node)) {
            return null;
        }

        if (! $node->isPublic()) {
            return null;
        }

        // special case, @see https://johannespichler.com/writing-custom-phpspec-matchers/
        if ($this->isNames($node, PhpSpecMethodName::RESERVED_CLASS_METHOD_NAMES)) {
            return null;
        }

        if (count((array) $node->stmts) !== 1) {
            return null;
        }

        $onlyStmt = $node->stmts[0] ?? null;
        if (! $onlyStmt instanceof Expression) {
            return null;
        }

        if (! $onlyStmt->expr instanceof MethodCall) {
            return null;
        }

        $methodCall = $onlyStmt->expr;
        if (! $this->isName($methodCall->name, PhpSpecMethodName::SHOULD_HAVE_TYPE)) {
            return null;
        }

        // remove method as no point
        return NodeTraverser::REMOVE_NODE;
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
}
