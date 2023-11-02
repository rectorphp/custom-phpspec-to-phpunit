<?php

declare(strict_types=1);

use Rector\Config\RectorConfig;
use Rector\PhpSpecToPHPUnit\Rector\Expression\ShouldNeverBeCalledRector;

return static function (RectorConfig $rectorConfig): void {
    $rectorConfig->rules([ShouldNeverBeCalledRector::class]);
};
