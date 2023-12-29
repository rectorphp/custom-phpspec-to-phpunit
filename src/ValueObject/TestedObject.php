<?php

declare(strict_types=1);

namespace Rector\PhpSpecToPHPUnit\ValueObject;

use PhpParser\Node\Name\FullyQualified;
use PHPStan\Type\ObjectType;

final class TestedObject
{
    /**
     * @param string[] $definedMockVariableNames
     */
    public function __construct(
        private readonly string $className,
        private readonly string $propertyName,
        private readonly ObjectType $testedObjectType,
        private readonly array $definedMockVariableNames
    ) {
    }

    public function getClassName(): string
    {
        return $this->className;
    }

    public function getPropertyName(): string
    {
        return $this->propertyName;
    }

    public function getTestedObjectType(): ObjectType
    {
        return $this->testedObjectType;
    }

    /**
     * @return string[]
     */
    public function getDefinedMockVariableNames(): array
    {
        return $this->definedMockVariableNames;
    }

    public function getTestedObjectFullyQualified(): FullyQualified
    {
        return new FullyQualified($this->className);
    }
}
