<?php

declare(strict_types=1);

use Rector\Config\RectorConfig;
use Rector\PhpSpecToPHPUnit\Rector\Class_\LetGoToTearDownClassMethodRector;
use Rector\PhpSpecToPHPUnit\Rector\Class_\LetToSetUpClassMethodRector;
use Rector\PhpSpecToPHPUnit\Rector\Class_\MoveParameterMockToPropertyMockRector;
use Rector\PhpSpecToPHPUnit\Rector\Class_\PhpSpecClassToPHPUnitClassRector;
use Rector\PhpSpecToPHPUnit\Rector\Class_\PhpSpecMocksToPHPUnitMocksRector;
use Rector\PhpSpecToPHPUnit\Rector\Class_\PhpSpecPromisesToPHPUnitAssertRector;
use Rector\PhpSpecToPHPUnit\Rector\ClassMethod\RenameTestMethodRector;
use Rector\PhpSpecToPHPUnit\Rector\ClassMethod\ShouldThrowAndInstantiationOrderRector;
use Rector\PhpSpecToPHPUnit\Rector\Namespace_\RenameSpecNamespacePrefixToTestRector;
use Rector\PhpSpecToPHPUnit\Rector\Variable\MockVariableToPropertyFetchRector;

/**
 * @see https://gnugat.github.io/2015/09/23/phpunit-with-phpspec.html
 * @see http://www.phpspec.net/en/stable/cookbook/construction.html
 */
return static function (RectorConfig $rectorConfig): void {
    $rectorConfig->rules([
        // detect be constructed through first
        RenameSpecNamespacePrefixToTestRector::class,

        // 0. must be first, as it removes methods
        \Rector\PhpSpecToPHPUnit\Rector\ClassMethod\RemoveShouldHaveTypeRector::class,

        // 1. first convert mocks
        PhpSpecMocksToPHPUnitMocksRector::class,
        PhpSpecPromisesToPHPUnitAssertRector::class,

        // 2. then methods
        LetToSetUpClassMethodRector::class,
        LetGoToTearDownClassMethodRector::class,
        ShouldThrowAndInstantiationOrderRector::class,
        RenameTestMethodRector::class,

        // 3. then the class itself
        MoveParameterMockToPropertyMockRector::class,
        MockVariableToPropertyFetchRector::class,

        // 4. this one must be last, as it rename parent class and changes spec type that is used to detect this class
        PhpSpecClassToPHPUnitClassRector::class,
    ]);
};
