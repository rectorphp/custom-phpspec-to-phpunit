<?php

declare(strict_types=1);

use Rector\Config\RectorConfig;
use Rector\PhpSpecToPHPUnit\Rector\Namespace_\RenameSpecNamespacePrefixToTestRector;

return static function (RectorConfig $rectorConfig): void {
    $rectorConfig->rules([RenameSpecNamespacePrefixToTestRector::class]);
};
