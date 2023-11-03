<?php

declare(strict_types=1);

namespace Rector\PhpSpecToPHPUnit\Naming;

use Rector\PhpSpecToPHPUnit\Enum\PhpSpecMethodName;
use Rector\PhpSpecToPHPUnit\Enum\PHPUnitMethodName;
use ReflectionClass;

final class SystemMethodDetector
{
    /**
     * @var array<class-string>
     */
    private const METHOD_NAME_CLASSES = [PhpSpecMethodName::class, PHPUnitMethodName::class];

    public static function detect(string $methodName): bool
    {
        foreach (self::METHOD_NAME_CLASSES as $methodNameClass) {
            $reflectionClass = new ReflectionClass($methodNameClass);

            foreach ($reflectionClass->getConstants() as $constantValue) {
                if ($methodName === $constantValue) {
                    return true;
                }
            }
        }

        return false;
    }
}
