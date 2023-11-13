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

    $rectorConfig->sets([
        \Rector\Set\ValueObject\SetList::INSTANCEOF,
        \Rector\Set\ValueObject\SetList::NAMING,
        \Rector\Set\ValueObject\SetList::TYPE_DECLARATION,
        \Rector\Set\ValueObject\SetList::DEAD_CODE,
        \Rector\Set\ValueObject\SetList::CODE_QUALITY,
        \Rector\Set\ValueObject\SetList::CODING_STYLE,
    ]);
    // $rectorConfig->sets([MigrationSetList::PHPSPEC_TO_PHPUNIT]);
};
