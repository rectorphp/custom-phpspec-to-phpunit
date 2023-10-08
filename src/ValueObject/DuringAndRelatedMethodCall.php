<?php

declare(strict_types=1);

namespace Rector\PhpSpecToPHPUnit\ValueObject;

use PhpParser\Node\Expr\MethodCall;

final class DuringAndRelatedMethodCall
{
    /**
     * @readonly
     * @var \PhpParser\Node\Expr\MethodCall
     */
    private $duringMethodCall;
    /**
     * @readonly
     * @var \PhpParser\Node\Expr\MethodCall
     */
    private $exceptionMethodCall;
    public function __construct(MethodCall $duringMethodCall, MethodCall $exceptionMethodCall)
    {
        $this->duringMethodCall = $duringMethodCall;
        $this->exceptionMethodCall = $exceptionMethodCall;
    }
    public function getDuringMethodCall(): MethodCall
    {
        return $this->duringMethodCall;
    }

    public function getExceptionMethodCall(): MethodCall
    {
        return $this->exceptionMethodCall;
    }
}
