<?php

declare(strict_types=1);

use Rector\Config\RectorConfig;
use Rector\PhpSpecToPHPUnit\Rector\MethodCall\RemoveShouldBeCalledRector;

return static function (RectorConfig $rectorConfig): void {
    $rectorConfig->rules([RemoveShouldBeCalledRector::class]);
};
