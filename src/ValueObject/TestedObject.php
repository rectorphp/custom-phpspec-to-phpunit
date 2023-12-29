<?php

declare(strict_types=1);

namespace Rector\PhpSpecToPHPUnit\ValueObject;

use PhpParser\Node\Name\FullyQualified;
use PHPStan\Type\ObjectType;

final class TestedObject
{
    /**
     * @readonly
     * @var string
     */
    private $className;
    /**
     * @readonly
     * @var string
     */
    private $propertyName;
    /**
     * @readonly
     * @var \PHPStan\Type\ObjectType
     */
    private $testedObjectType;
    /**
     * @var string[]
     * @readonly
     */
    private $definedMockVariableNames;
    /**
     * @param string[] $definedMockVariableNames
     */
    public function __construct(string $className, string $propertyName, ObjectType $testedObjectType, array $definedMockVariableNames)
    {
        $this->className = $className;
        $this->propertyName = $propertyName;
        $this->testedObjectType = $testedObjectType;
        $this->definedMockVariableNames = $definedMockVariableNames;
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
