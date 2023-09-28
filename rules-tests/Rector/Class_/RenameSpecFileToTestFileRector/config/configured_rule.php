<?php

declare(strict_types=1);

use Rector\PhpSpecToPHPUnit\Rector\Class_\RenameSpecFileToTestFileRector;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;

return static function (ContainerConfigurator $containerConfigurator): void {
    $services = $containerConfigurator->services();
    $services->set(RenameSpecFileToTestFileRector::class);
};
