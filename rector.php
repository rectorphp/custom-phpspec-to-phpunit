<?php

declare(strict_types=1);

use Rector\Config\RectorConfig;
use Rector\Php55\Rector\String_\StringClassNameToClassConstantRector;

return RectorConfig::configure()
    ->withImportNames()
    ->withPaths([__DIR__ . '/config', __DIR__ . '/src', __DIR__ . '/rules', __DIR__ . '/rules-tests'])
    ->withSkip([
        StringClassNameToClassConstantRector::class => [__DIR__ . '/src/DocFactory.php'],
    ])
    ->withPreparedSets(
        instanceOf: true,
        naming: true,
        typeDeclarations: true,
        deadCode: true,
        codeQuality: true,
        codingStyle: true,
    );
