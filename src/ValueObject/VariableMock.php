<?php

declare(strict_types=1);

namespace Rector\PhpSpecToPHPUnit\ValueObject;

final class VariableMock
{
    public function __construct(
        private string $methodName,
        private string $variableName,
        private ?string $mockClassName
    ) {
    }

    public function getMethodName(): string
    {
        return $this->methodName;
    }

    public function getVariableName(): string
    {
        return $this->variableName;
    }

    public function getMockClassName(): ?string
    {
        return $this->mockClassName;
    }
}
