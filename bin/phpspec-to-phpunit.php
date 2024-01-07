<?php

declare(strict_types=1);

use Rector\PhpSpecToPHPUnit\Command\RenameSuffixCommand;
use Symfony\Component\Console\Application;

require __DIR__ . '/../vendor/autoload.php';

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
