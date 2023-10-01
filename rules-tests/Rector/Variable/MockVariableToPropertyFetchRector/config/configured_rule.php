<?php

declare(strict_types=1);

use Rector\Config\RectorConfig;
use Rector\PhpSpecToPHPUnit\Rector\Variable\MockVariableToPropertyFetchRector;

return static function (RectorConfig $rectorConfig): void {
    $rectorConfig->rules([
        MockVariableToPropertyFetchRector::class,
    ]);
};
