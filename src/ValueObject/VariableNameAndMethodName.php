<?php

declare(strict_types=1);

namespace Rector\PhpSpecToPHPUnit\ValueObject;

final class VariableNameAndMethodName
{
    /**
     * @var string
     */
    private $variableName;
    /**
     * @var string
     */
    private $methodName;
    public function __construct(string $variableName, string $methodName)
    {
        $this->variableName = $variableName;
        $this->methodName = $methodName;
    }

    public function getVariableName(): string
    {
        return $this->variableName;
    }

    public function getMethodName(): string
    {
        return $this->methodName;
    }

    public function getHash(): string
    {
        return $this->variableName . '_' . $this->methodName;
    }
}
