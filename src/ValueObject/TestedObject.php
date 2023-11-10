<?php

declare(strict_types=1);

namespace Rector\PhpSpecToPHPUnit\ValueObject;

use PHPStan\Type\ObjectType;

final class TestedObject
{
    public function __construct(
        private readonly string $className,
        private readonly string $propertyName,
        private readonly ObjectType $testedObjectType
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
}
