<?php

declare(strict_types=1);

use Rector\Config\RectorConfig;
use Rector\PhpSpecToPHPUnit\Rector\ClassMethod\ShouldThrowAndInstantiationOrderRector;

return static function (RectorConfig $rectorConfig): void {
    $rectorConfig->rules([ShouldThrowAndInstantiationOrderRector::class]);
};
