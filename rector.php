<?php

declare(strict_types=1);

use Rector\Config\RectorConfig;
use Rector\Php55\Rector\String_\StringClassNameToClassConstantRector;
use Rector\Set\ValueObject\SetList;

return static function (RectorConfig $rectorConfig): void {
    $rectorConfig->importNames();

    $rectorConfig->paths([__DIR__ . '/config', __DIR__ . '/src', __DIR__ . '/rules', __DIR__ . '/rules-tests']);

    $rectorConfig->skip([
        // for tests
        '*/Source/*',

        StringClassNameToClassConstantRector::class => [__DIR__ . '/src/DocFactory.php'],
    ]);

    $rectorConfig->sets([
        SetList::INSTANCEOF,
        SetList::NAMING,
        SetList::TYPE_DECLARATION,
        SetList::DEAD_CODE,
        SetList::CODE_QUALITY,
        SetList::CODING_STYLE,
    ]);
};
