<?php

declare(strict_types=1);

namespace Rector\PhpSpecToPHPUnit\ValueObject;

use PhpParser\Node\Expr\MethodCall;

final class ConsecutiveMethodCall
{
    /**
     * @readonly
     */
    private int $key;
    /**
     * @readonly
     */
    private string $variableName;
    /**
     * @readonly
     */
    private string $methodName;
    /**
     * @readonly
     */
    private MethodCall $methodCall;
    public function __construct(int $key, string $variableName, string $methodName, MethodCall $methodCall)
    {
        $this->key = $key;
        $this->variableName = $variableName;
        $this->methodName = $methodName;
        $this->methodCall = $methodCall;
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
