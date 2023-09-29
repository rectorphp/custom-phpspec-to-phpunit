<?php

declare(strict_types=1);

use Rector\Config\RectorConfig;
use Rector\PhpSpecToPHPUnit\Rector\Class_\AddMockPropertiesRector;
use Rector\PhpSpecToPHPUnit\Rector\Class_\PhpSpecClassToPHPUnitClassRector;
use Rector\PhpSpecToPHPUnit\Rector\ClassMethod\PhpSpecMethodToPHPUnitMethodRector;
use Rector\PhpSpecToPHPUnit\Rector\MethodCall\PhpSpecMocksToPHPUnitMocksRector;
use Rector\PhpSpecToPHPUnit\Rector\MethodCall\PhpSpecPromisesToPHPUnitAssertRector;
use Rector\PhpSpecToPHPUnit\Rector\Variable\MockVariableToPropertyFetchRector;

// * @changelog https://gnugat.github.io/2015/09/23/phpunit-with-phpspec.html
// * @changelog http://www.phpspec.net/en/stable/cookbook/construction.html

// see: https://gnugat.github.io/2015/09/23/phpunit-with-phpspec.html
return static function (RectorConfig $rectorConfig): void {
    $rectorConfig->rules([
        \Rector\PhpSpecToPHPUnit\Rector\Namespace_\RenameSpecNamespacePrefixToTestRector::class,

        // 1. first convert mocks
        PhpSpecMocksToPHPUnitMocksRector::class,
        PhpSpecPromisesToPHPUnitAssertRector::class,

        // 2. then methods
        PhpSpecMethodToPHPUnitMethodRector::class,

        // 3. then the class itself
        PhpSpecClassToPHPUnitClassRector::class,
        AddMockPropertiesRector::class,
        MockVariableToPropertyFetchRector::class,
    ]);
};
