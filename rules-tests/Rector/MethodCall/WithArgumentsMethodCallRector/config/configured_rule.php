<?php

declare(strict_types=1);

use Rector\Config\RectorConfig;
use Rector\PhpSpecToPHPUnit\Rector\MethodCall\WithArgumentsMethodCallRector;

return static function (RectorConfig $rectorConfig): void {
    $rectorConfig->rules([WithArgumentsMethodCallRector::class]);
};
