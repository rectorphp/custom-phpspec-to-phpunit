<?php

declare(strict_types=1);

namespace Rector\PhpSpecToPHPUnit\ValueObject;

use Stringable;

final class ServiceMock
{
    /**
     * @readonly
     * @var string
     */
    private $variableName;
    /**
     * @readonly
     * @var string
     */
    private $mockClassName;
    public function __construct(string $variableName, string $mockClassName)
    {
        $this->variableName = $variableName;
        $this->mockClassName = $mockClassName;
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
