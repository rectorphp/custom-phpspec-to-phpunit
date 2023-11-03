<?php

declare(strict_types=1);

namespace Rector\PhpSpecToPHPUnit\NodeFactory;

use PhpParser\Node\Arg;
use PhpParser\Node\Expr;
use PhpParser\Node\Expr\MethodCall;
use PhpParser\Node\Expr\Variable;
use PhpParser\Node\Scalar\String_;
use Rector\PhpSpecToPHPUnit\Enum\PHPUnitMethodName;

final class ExpectsCallFactory
{
    public static function createExpectsOnceCall(Expr $callerExpr): MethodCall
    {
        $thisOnceMethodCall = new MethodCall(new Variable('this'), PHPUnitMethodName::ONCE);
        $args = [new Arg($thisOnceMethodCall)];

        return new MethodCall($callerExpr, PHPUnitMethodName::EXPECTS, $args);
    }

    public static function createMethodCall(Expr $callerExpr, string $methodName): MethodCall
    {
        $args = [new Arg(new String_($methodName))];

        return new MethodCall($callerExpr, 'method', $args);
    }
}
