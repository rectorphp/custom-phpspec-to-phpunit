<?php

declare(strict_types=1);

use Rector\Config\RectorConfig;
use Rector\PhpSpecToPHPUnit\Rector\Expression\ExpectedMockDeclarationRector;

return static function (RectorConfig $rectorConfig): void {
    $rectorConfig->rules([ExpectedMockDeclarationRector::class]);
};
