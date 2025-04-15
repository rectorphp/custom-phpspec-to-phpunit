<?php

declare(strict_types=1);

namespace Rector\PhpSpecToPHPUnit\NodeFactory;

use Rector\PhpSpecToPHPUnit\Exception\ShouldNotHappenException;
use PhpParser\Node\Arg;
use PhpParser\Node\Expr;
use PhpParser\Node\Expr\ClassConstFetch;
use PhpParser\Node\Expr\MethodCall;
use PhpParser\Node\Expr\New_;
use PhpParser\Node\Expr\PropertyFetch;
use PhpParser\Node\Expr\Variable;
use PhpParser\Node\Name\FullyQualified;
use PhpParser\Node\Stmt\Class_;
use PhpParser\Node\Stmt\Expression;
use Rector\PhpSpecToPHPUnit\Enum\PHPUnitMethodName;
use Rector\PhpSpecToPHPUnit\ValueObject\DuringAndRelatedMethodCall;
use Rector\PhpSpecToPHPUnit\ValueObject\TestedObject;
use Rector\ValueObject\MethodName;

final class ExpectExceptionMethodCallFactory
{
    /**
     * @return array<Expression<MethodCall>>
     */
    public function createExpectExceptionStmts(DuringAndRelatedMethodCall $duringAndRelatedMethodCall): array
    {
        $nestedMethodCall = $duringAndRelatedMethodCall->getExceptionMethodCall();

        $firstArg = $nestedMethodCall->getArgs()[0];
        $expectExceptionMethodCall = $this->createThisExpectException($firstArg->value);

        $expressions = [new Expression($expectExceptionMethodCall)];

        $expectExceptionMessageMethodCall = $this->createThisExpectExceptionMessage($firstArg->value);
        if ($expectExceptionMessageMethodCall instanceof MethodCall) {
            $expressions[] = new Expression($expectExceptionMessageMethodCall);
        }

        return $expressions;
    }

    /**
     * @return Expression<MethodCall>
     */
    public function createMethodCallStmt(
        DuringAndRelatedMethodCall $duringAndRelatedMethodCall,
        TestedObject $testedObject
    ): Expression {
        $exceptionMethodCall = $duringAndRelatedMethodCall->getExceptionMethodCall();

        if ($exceptionMethodCall->var instanceof PropertyFetch) {
            $callerExpr = new Variable($exceptionMethodCall->var->name->toString());
        } else {
            // fallback just in case
            $callerExpr = $exceptionMethodCall->var;
        }

        $calledMethodName = $duringAndRelatedMethodCall->getCalledMethodName();
        $calledArgs = $duringAndRelatedMethodCall->getCalledArgs();

        if ($calledMethodName === MethodName::CONSTRUCT) {
            // special case with new
            $new = new New_(new FullyQualified($testedObject->getClassName()), $calledArgs);
            return new Expression($new);
        }

        $objectMethodCall = new MethodCall($callerExpr, $calledMethodName, $calledArgs);

        return new Expression($objectMethodCall);
    }

    private function createThisExpectException(Expr $expr): MethodCall
    {
        if ($expr instanceof New_) {
            if ($expr->class instanceof Class_) {
                throw new ShouldNotHappenException();
            }

            $arg = new Arg(new ClassConstFetch($expr->class, 'class'));
        } else {
            $arg = new Arg($expr);
        }

        return new MethodCall(new Variable('this'), PHPUnitMethodName::EXPECT_EXCEPTION, [$arg]);
    }

    private function createThisExpectExceptionMessage(Expr $expr): ?MethodCall
    {
        if (! $expr instanceof New_) {
            return null;
        }

        if ($expr->args === []) {
            return null;
        }

        /** @var Arg $firstArg */
        $firstArg = $expr->args[0];

        return new MethodCall(new Variable('this'), 'expectExceptionMessage', [$firstArg]);
    }
}
