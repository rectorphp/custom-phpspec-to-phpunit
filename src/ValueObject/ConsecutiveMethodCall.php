<?php

declare(strict_types=1);

namespace Rector\PhpSpecToPHPUnit\ValueObject;

use PhpParser\Node\Expr\MethodCall;

final class ConsecutiveMethodCall
{
    public function __construct(
        private readonly int $key,
        private readonly string $variableName,
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

    public function getMockVariableName(): string
    {
        return $this->variableName;
    }

    public function getMethodCall(): MethodCall
    {
        return $this->methodCall;
    }
}
