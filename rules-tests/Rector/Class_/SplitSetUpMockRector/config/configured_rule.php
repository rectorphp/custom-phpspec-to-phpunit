<?php

declare(strict_types=1);

use Rector\Config\RectorConfig;
use Rector\PhpSpecToPHPUnit\Rector\Class_\SplitSetUpMockRector;

return static function (RectorConfig $rectorConfig): void {
    $rectorConfig->rules([SplitSetUpMockRector::class]);
};
