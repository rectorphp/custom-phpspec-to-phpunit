<?php

declare(strict_types=1);

use Rector\Config\RectorConfig;
use Rector\PhpSpecToPHPUnit\Rector\Class_\CompleteMissingSetUpPropertyRector;

return static function (RectorConfig $rectorConfig): void {
    $rectorConfig->rules([CompleteMissingSetUpPropertyRector::class]);
};
