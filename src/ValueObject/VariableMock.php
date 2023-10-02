<?php

declare(strict_types=1);

namespace Rector\PhpSpecToPHPUnit\ValueObject;

use Stringable;

final class VariableMock implements Stringable
{
    public function __construct(
        private readonly string $variableName,
        private readonly string $mockClassName
    ) {
    }

    /**
     * To enable in array unique
     */
    public function __toString(): string
    {
        return $this->variableName;
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
