<?php

declare(strict_types=1);

use Rector\Config\RectorConfig;
use Rector\PhpSpecToPHPUnit\Rector\Class_\MoveParameterMockToPropertyMockRector;
use Rector\PhpSpecToPHPUnit\Rector\Class_\PhpSpecClassToPHPUnitClassRector;
use Rector\PhpSpecToPHPUnit\Rector\Class_\PhpSpecPromisesToPHPUnitAssertRector;
use Rector\PhpSpecToPHPUnit\Rector\ClassMethod\RenameTestMethodRector;
use Rector\PhpSpecToPHPUnit\Rector\MethodCall\PhpSpecMocksToPHPUnitMocksRector;
use Rector\PhpSpecToPHPUnit\Rector\Namespace_\RenameSpecNamespacePrefixToTestRector;
use Rector\PhpSpecToPHPUnit\Rector\Variable\MockVariableToPropertyFetchRector;

/**
 * @see https://gnugat.github.io/2015/09/23/phpunit-with-phpspec.html
 * @see http://www.phpspec.net/en/stable/cookbook/construction.html
 */
return static function (RectorConfig $rectorConfig): void {
    $rectorConfig->rules([
        //        RenameSpecNamespacePrefixToTestRector::class,

        // 1. first convert mocks
        PhpSpecMocksToPHPUnitMocksRector::class,
        PhpSpecPromisesToPHPUnitAssertRector::class,

        // 2. then methods
        RenameTestMethodRector::class,

        // 3. then the class itself
        PhpSpecClassToPHPUnitClassRector::class,
        MoveParameterMockToPropertyMockRector::class,
        MockVariableToPropertyFetchRector::class,
    ]);
};
