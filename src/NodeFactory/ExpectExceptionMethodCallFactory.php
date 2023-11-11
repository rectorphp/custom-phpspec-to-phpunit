<?php

declare(strict_types=1);

namespace Rector\PhpSpecToPHPUnit\NodeFactory;

use PhpParser\Node\Arg;
use PhpParser\Node\Expr\MethodCall;
use PhpParser\Node\Expr\PropertyFetch;
use PhpParser\Node\Expr\Variable;
use PhpParser\Node\Stmt\Expression;
use Rector\PhpSpecToPHPUnit\ValueObject\DuringAndRelatedMethodCall;

final class ExpectExceptionMethodCallFactory
{
    /**
     * @return Expression<MethodCall>
     */
    public function createExpectsException(DuringAndRelatedMethodCall $duringAndRelatedMethodCall): Expression
    {
        $nestedMethodCall = $duringAndRelatedMethodCall->getExceptionMethodCall();
        $firstArg = $nestedMethodCall->getArgs()[0];

        $thisExpectExceptionMethodCall = new MethodCall(new Variable('this'), 'expectException');
        $thisExpectExceptionMethodCall->args[] = new Arg($firstArg->value);

        return new Expression($thisExpectExceptionMethodCall);
    }

    /**
     * @return Expression<MethodCall>
     */
    public function createMethodCallStmt(DuringAndRelatedMethodCall $duringAndRelatedMethodCall): Expression
    {
        $exceptionMethodCall = $duringAndRelatedMethodCall->getExceptionMethodCall();

        if ($exceptionMethodCall->var instanceof PropertyFetch) {
            $callerExpr = new Variable($exceptionMethodCall->var->name->toString());
        } else {
            // fallback just in case
            $callerExpr = $exceptionMethodCall->var;
        }

        $calledMethodName = $duringAndRelatedMethodCall->getCalledMethodName();
        $calledArgs = $duringAndRelatedMethodCall->getCalledArgs();

        $objectMethodCall = new MethodCall($callerExpr, $calledMethodName, $calledArgs);
        return new Expression($objectMethodCall);
    }
}
