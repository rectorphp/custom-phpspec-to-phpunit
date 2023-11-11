<?php

declare(strict_types=1);

namespace Rector\PhpSpecToPHPUnit\NodeFactory;

use PhpParser\Node\Arg;
use PhpParser\Node\Expr\Array_;
use PhpParser\Node\Expr\MethodCall;
use PhpParser\Node\Expr\PropertyFetch;
use PhpParser\Node\Expr\Variable;
use PhpParser\Node\Stmt\Expression;
use Rector\Core\PhpParser\Node\NodeFactory;
use Rector\Core\PhpParser\Node\Value\ValueResolver;
use Rector\PhpSpecToPHPUnit\ValueObject\DuringAndRelatedMethodCall;

final class ExpectExceptionMethodCallFactory
{
    public function __construct(
        private readonly NodeFactory $nodeFactory,
        private readonly ValueResolver $valueResolver,
    ) {
    }

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
        $duringMethodCall = $duringAndRelatedMethodCall->getDuringMethodCall();
        $exceptionMethodCall = $duringAndRelatedMethodCall->getExceptionMethodCall();

        $args = $duringMethodCall->getArgs();
        $firstArg = $args[0];

        if ($exceptionMethodCall->var instanceof PropertyFetch) {
            $callerExpr = new Variable($exceptionMethodCall->var->name->toString());
        } else {
            // fallback just in case
            $callerExpr = $exceptionMethodCall->var;
        }

        // include arguments too
        $methodName = $this->valueResolver->getValue($firstArg->value);
        $newArgs = $this->resolveMethodCallArgs($args);

        $objectMethodCall = new MethodCall($callerExpr, $methodName, $newArgs);

        return new Expression($objectMethodCall);
    }

    /**
     * @param Arg[] $args
     * @return Arg[]
     */
    private function resolveMethodCallArgs(array $args): array
    {
        if (! isset($args[1])) {
            return [];
        }

        $secondArg = $args[1];
        if (! $secondArg->value instanceof Array_) {
            return [];
        }

        $array = $secondArg->value;
        return $this->nodeFactory->createArgs($array->items);
    }
}
