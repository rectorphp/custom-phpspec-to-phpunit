<?php

declare(strict_types=1);

use Symplify\EasyCodingStandard\Config\ECSConfig;

return ECSConfig::configure()
    ->withPreparedSets(symplify: true, common: true, psr12: true)
    ->withPaths([
        __DIR__ . '/src',
        __DIR__ . '/rules',
        __DIR__ . '/rules-tests',
        __DIR__ . '/config',
        __DIR__ . '/bin',
    ])
    ->withRootFiles()
    ->withSkip(['*/Source/*', '*/Fixture/*', '*/Expected/*']);
