<?php

declare(strict_types=1);

namespace Rector\PhpSpecToPHPUnit\ValueObject;

use PhpParser\Node\Expr\MethodCall;
use PhpParser\Node\Expr\Variable;
use Rector\Core\Exception\ShouldNotHappenException;

final class ConsecutiveMethodCall
{
    public function __construct(
        private readonly int $key,
        private readonly string $methodName,
        private readonly MethodCall $methodCall
    ) {
    }

    public function getKey(): int
    {
        return $this->key;
    }

    public function getMethodName(): string
    {
        return $this->methodName;
    }

    public function getMockVariable(): Variable
    {
        $methodCall = $this->methodCall;

        while ($methodCall->var instanceof MethodCall) {
            $methodCall = $methodCall->var;
        }

        if (! $methodCall->var instanceof Variable) {
            throw new ShouldNotHappenException();
        }

        return $methodCall->var;
    }

    public function getMethodCall(): MethodCall
    {
        return $this->methodCall;
    }
}
