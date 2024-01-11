<?php

declare(strict_types=1);

use Rector\Config\RectorConfig;

return static function (RectorConfig $rectorConfig): void {
    $rectorConfig->ruleWithConfiguration(\Rector\PhpSpecToPHPUnit\Rector\Class_\LearnFromPHPUnitReportRector::class, [

    ]);
};
