<?php

declare(strict_types=1);

use Rector\Config\RectorConfig;
use Rector\PhpSpecToPHPUnit\Rector\Class_\MoveParameterMockToPropertyMockRector;

return static function (RectorConfig $rectorConfig): void {
    $rectorConfig->rules([MoveParameterMockToPropertyMockRector::class]);
};
