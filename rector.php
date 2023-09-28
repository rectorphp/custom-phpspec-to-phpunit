<?php

declare(strict_types=1);

use Rector\Config\RectorConfig;
use Rector\Set\ValueObject\LevelSetList;
use Rector\Set\ValueObject\SetList;

return static function (RectorConfig $rectorConfig): void {
    $rectorConfig->importNames();

    $rectorConfig->paths([__DIR__ . '/config', __DIR__ . '/src', __DIR__ . '/rules', __DIR__ . '/rules-tests']);

    $rectorConfig->skip([
        // for tests
        '*/Source/*',
        '*/Fixture/*',
    ]);

    $rectorConfig->sets([
        LevelSetList::UP_TO_PHP_81,
        SetList::DEAD_CODE,
        SetList::CODE_QUALITY,
        SetList::CODING_STYLE,
        SetList::EARLY_RETURN,
        SetList::NAMING,
        SetList::TYPE_DECLARATION,
        SetList::PRIVATIZATION,
    ]);
};
