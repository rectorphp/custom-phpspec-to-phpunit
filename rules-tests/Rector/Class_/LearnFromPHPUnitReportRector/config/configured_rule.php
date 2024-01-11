<?php

declare(strict_types=1);

use Rector\Config\RectorConfig;
use Rector\PhpSpecToPHPUnit\Rector\Class_\LearnFromPHPUnitReportRector;

return static function (RectorConfig $rectorConfig): void {
    $rectorConfig->ruleWithConfiguration(LearnFromPHPUnitReportRector::class, [
        __DIR__ . '/../Source/phpunit-report.txt',
    ]);
};
