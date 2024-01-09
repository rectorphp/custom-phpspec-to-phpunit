<?php

declare(strict_types=1);

use Rector\Config\RectorConfig;
use Rector\PhpSpecToPHPUnit\Rector\Class_\CompleteMissingSetUpPropertyRector;
use Rector\PhpSpecToPHPUnit\Rector\Class_\SplitSetUpMockRector;

// run this set after the flip is done, to tidy up the rest with separated context
return static function (RectorConfig $rectorConfig): void {
    $rectorConfig->rules([CompleteMissingSetUpPropertyRector::class, SplitSetUpMockRector::class]);
};
