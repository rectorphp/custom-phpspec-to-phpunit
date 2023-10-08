<?php

declare(strict_types=1);

namespace Rector\PhpSpecToPHPUnit\Rector\ClassMethod;

use PhpParser\Node;
use PhpParser\Node\Arg;
use PhpParser\Node\Expr\MethodCall;
use PhpParser\Node\Expr\Variable;
use PhpParser\Node\Stmt\ClassMethod;
use PhpParser\Node\Stmt\Expression;
use Rector\Core\Rector\AbstractRector;
use Rector\PhpSpecToPHPUnit\Enum\PhpSpecMethodName;
use Rector\PhpSpecToPHPUnit\NodeAnalyzer\DuringAndRelatedMethodCallMatcher;
use Rector\PhpSpecToPHPUnit\NodeAnalyzer\PhpSpecBehaviorNodeDetector;
use Rector\PhpSpecToPHPUnit\ValueObject\DuringAndRelatedMethodCall;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;

/**
 * @see \Rector\PhpSpecToPHPUnit\Tests\Rector\ClassMethod\ShouldThrowAndInstantiationOrderRector\ShouldThrowAndInstantiationOrderRectorTest
 */
final class ShouldThrowAndInstantiationOrderRector extends AbstractRector
{
    public function __construct(
        private readonly PhpSpecBehaviorNodeDetector $phpSpecBehaviorNodeDetector,
        private readonly DuringAndRelatedMethodCallMatcher $duringAndRelatedMethodCallMatcher,
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
        if ($this->isNames($node, PhpSpecMethodName::RESERVED_CLASS_METHOD_NAMES)) {
            return null;
        }

        $hasChanged = false;

        foreach ((array) $node->stmts as $key => $stmt) {
            if (! $stmt instanceof Expression) {
                continue;
            }

            $duringAndRelatedMethodCall = $this->duringAndRelatedMethodCallMatcher->match(
                $stmt,
                PhpSpecMethodName::DURING_INSTANTIATION
            );
            if (! $duringAndRelatedMethodCall instanceof DuringAndRelatedMethodCall) {
                continue;
            }

            $previousStmt = $node->stmts[$key - 1] ?? null;
            if (! $previousStmt instanceof Node\Stmt) {
                continue;
            }

            // move previous expression here
            $node->stmts[$key - 1] = $this->createExpectExceptionStmt(
                $duringAndRelatedMethodCall->getExceptionMethodCall()
            );
            $node->stmts[$key] = $previousStmt;

            $hasChanged = true;
            break;
        }

        if (! $hasChanged) {
            return null;
        }

        return $node;
    }

    public function getRuleDefinition(): RuleDefinition
    {
        return new RuleDefinition('Reorder and rename shouldThrow() method to mark before instantiation', [
            new CodeSample(
                <<<'CODE_SAMPLE'
use PhpSpec\ObjectBehavior;

class RenameMethodTest extends ObjectBehavior
{
    public function is_should()
    {
        $this->beConstructedThrough('create', [$data]);
        $this->shouldThrow(ValidationException::class)->duringInstantiation();
    }
}
CODE_SAMPLE
                ,
                <<<'CODE_SAMPLE'
use PhpSpec\ObjectBehavior;

class RenameMethodTest extends ObjectBehavior
{
    public function is_should()
    {
        $this->expectException(ValidationException::class);
        $this->beConstructedThrough('create', [$data]);
    }
}
CODE_SAMPLE
            ),
        ]);
    }

    private function createExpectExceptionStmt(MethodCall $nestedMethodCall): Expression
    {
        $thisExpectExceptionMethodCall = new MethodCall(new Variable('this'), 'expectException');
        $thisExpectExceptionMethodCall->args[] = new Arg($nestedMethodCall->getArgs()[0]->value);

        return new Expression($thisExpectExceptionMethodCall);
    }
}
