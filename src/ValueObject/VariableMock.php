<?php

declare(strict_types=1);

namespace Rector\PhpSpecToPHPUnit\ValueObject;

final class VariableMock implements \Stringable
{
    public function __construct(
        private string $methodName,
        private string $variableName,
        private string $mockClassName
    ) {
    }

    /**
     * To enable in array unique
     */
    public function __toString(): string
    {
        return $this->variableName;
    }

    public function getMethodName(): string
    {
        return $this->methodName;
    }

    public function getVariableName(): string
    {
        return $this->variableName;
    }

    public function getMockClassName(): string
    {
        return $this->mockClassName;
    }
}
