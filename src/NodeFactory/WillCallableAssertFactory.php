<?php

declare(strict_types=1);

namespace Rector\PhpSpecToPHPUnit\NodeFactory;

use PhpParser\Node;
use PhpParser\Node\Arg;
use PhpParser\Node\Expr\BinaryOp;
use PhpParser\Node\Expr\BinaryOp\Equal;
use PhpParser\Node\Expr\BinaryOp\Identical;
use PhpParser\Node\Expr\Closure;
use PhpParser\Node\Expr\ConstFetch;
use PhpParser\Node\Expr\MethodCall;
use PhpParser\Node\Expr\Variable;
use PhpParser\Node\Name;
use PhpParser\Node\Scalar;
use PhpParser\Node\Stmt\Expression;
use PhpParser\Node\Stmt\Return_;
use PhpParser\NodeFinder;
use Rector\PhpSpecToPHPUnit\Enum\PHPUnitMethodName;

final class WillCallableAssertFactory
{
    public function __construct(
        private readonly NodeFinder $nodeFinder,
    ) {
    }

    public function create(MethodCall $methodCall, Closure $closure): MethodCall
    {
        // collect all expectations and turn them to assertSame() or similar
        /** @var BinaryOp[] $binaryOps */
        $binaryOps = $this->nodeFinder->find($closure->stmts, static function (Node $node): bool {
            if (! $node instanceof BinaryOp) {
                return false;
            }

            if ($node->right instanceof ConstFetch) {
                return true;
            }

            return $node->right instanceof Scalar;
        });

        $assertSameExpressions = $this->createAssertSameExpressions($binaryOps);

        // return true in the end, to comply with PHPUnit
        $closure->stmts = $assertSameExpressions;
        $closure->stmts[] = new Return_(new ConstFetch(new Name('true')));

        $callableArgs = [new Arg($closure)];

        $callableWrapMethodCall = new MethodCall(new Variable('this'), PHPUnitMethodName::CALLALBLE, $callableArgs);
        return new MethodCall($methodCall, PHPUnitMethodName::WITH, [new Arg($callableWrapMethodCall)]);
    }

    /**
     * @param BinaryOp[] $binaryOps
     * @return array<Expression<MethodCall>>
     */
    private function createAssertSameExpressions(array $binaryOps): array
    {
        $assertSameMethodCallExpressions = [];

        // turn binary ops to assertSame()
        foreach ($binaryOps as $binaryOp) {
            if ($binaryOp instanceof Identical || $binaryOp instanceof Equal) {
                $methodName = PHPUnitMethodName::ASSERT_SAME;
            } else {
                $methodName = PHPUnitMethodName::ASSERT_NOT_SAME;
            }

            $assertSameMethodCall = new MethodCall(new Variable('this'), $methodName, [
                // put expected value first as typical in PHPUnit
                new Arg($binaryOp->right),
                new Arg($binaryOp->left),
            ]);

            $assertSameMethodCallExpressions[] = new Expression($assertSameMethodCall);
        }

        return $assertSameMethodCallExpressions;
    }
}
