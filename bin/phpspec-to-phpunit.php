<?php

declare(strict_types=1);

use Rector\PhpSpecToPHPUnit\Command\RenameSuffixCommand;
use Symfony\Component\Console\Application;

$possibleAutoloadPaths = [
    // dependency
    __DIR__ . '/../../../autoload.php',
    // monorepo
    __DIR__ . '/../../../vendor/autoload.php',
    // after split package
    __DIR__ . '/../vendor/autoload.php',
];

foreach ($possibleAutoloadPaths as $possibleAutoloadPath) {
    if (file_exists($possibleAutoloadPath)) {
        require_once $possibleAutoloadPath;
        break;
    }
}

$application = new Application();
$application->add(new RenameSuffixCommand());

// hide irrelevant commands
$application->get('help')
    ->setHidden(true);
$application->get('completion')
    ->setHidden(true);
$application->get('list')
    ->setHidden(true);

exit($application->run());
