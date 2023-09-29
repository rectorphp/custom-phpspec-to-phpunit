<?php

declare(strict_types=1);

use Rector\Config\RectorConfig;
use Rector\PhpSpecToPHPUnit\Rector\Class_\AddMockPropertiesRector;
use Rector\PhpSpecToPHPUnit\Rector\Class_\PhpSpecClassToPHPUnitClassRector;
use Rector\PhpSpecToPHPUnit\Rector\ClassMethod\PhpSpecMethodToPHPUnitMethodRector;
use Rector\PhpSpecToPHPUnit\Rector\MethodCall\PhpSpecMocksToPHPUnitMocksRector;
use Rector\PhpSpecToPHPUnit\Rector\MethodCall\PhpSpecPromisesToPHPUnitAssertRector;
use Rector\PhpSpecToPHPUnit\Rector\Variable\MockVariableToPropertyFetchRector;

return static function (RectorConfig $rectorConfig): void {
    $rectorConfig->rules([
//        \Rector\PhpSpecToPHPUnit\Rector\Namespace_\RenameSpecNamespacePrefixToTestRector::class,
//        PhpSpecMocksToPHPUnitMocksRector::class,
//        PhpSpecPromisesToPHPUnitAssertRector::class,
//        PhpSpecMethodToPHPUnitMethodRector::class,
//        PhpSpecClassToPHPUnitClassRector::class,
//        AddMockPropertiesRector::class,
        MockVariableToPropertyFetchRector::class,
    ]);
};
