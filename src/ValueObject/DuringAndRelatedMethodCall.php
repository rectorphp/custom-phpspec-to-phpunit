<?php

declare(strict_types=1);

namespace Rector\PhpSpecToPHPUnit\ValueObject;

use PhpParser\Node\Expr\MethodCall;

final class DuringAndRelatedMethodCall
{
    public function __construct(
        private readonly MethodCall $duringMethodCall,
        private readonly MethodCall $exceptionMethodCall,
    ) {
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
