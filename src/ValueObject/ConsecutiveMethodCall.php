<?php

declare(strict_types=1);

namespace Rector\PhpSpecToPHPUnit\ValueObject;

use PhpParser\Node\Expr\MethodCall;

final class ConsecutiveMethodCall
{
    /**
     * @readonly
     * @var int
     */
    private $key;
    /**
     * @readonly
     * @var string
     */
    private $variableName;
    /**
     * @readonly
     * @var string
     */
    private $methodName;
    /**
     * @readonly
     * @var \PhpParser\Node\Expr\MethodCall
     */
    private $methodCall;
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
