<?php

declare(strict_types=1);

use Rector\Config\RectorConfig;
use Rector\PhpSpecToPHPUnit\Set\MigrationSetList;

return static function (RectorConfig $rectorConfig): void {
    $rectorConfig->importNames();

    $rectorConfig->paths([__DIR__ . '/config', __DIR__ . '/src', __DIR__ . '/rules', __DIR__ . '/rules-tests']);

    $rectorConfig->skip([
        // for tests
        '*/Source/*',
    ]);

    $rectorConfig->sets([MigrationSetList::PHPSPEC_TO_PHPUNIT]);
};
