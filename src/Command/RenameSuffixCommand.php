<?php

declare(strict_types=1);

namespace Rector\PhpSpecToPHPUnit\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\Finder\Finder;

/**
 * @api use din bin
 */
final class RenameSuffixCommand extends Command
{
    protected function configure(): void
    {
        $this->setName('rename-suffix');
        $this->setDescription('Rename "*Spec.php" files to "*Test.php" files, if class and file path do no match');
        $this->addArgument('source', InputArgument::REQUIRED, 'Path to source directory');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $symfonyStyle = new SymfonyStyle($input, $output);

        $source = (string) $input->getArgument('source');

        $formerSpecFileFinder = Finder::create()
            ->files()
            ->name('*Spec.php')
            ->in($source)
            ->getIterator();

        foreach ($formerSpecFileFinder as $fileInfo) {
            // get short file name without suffix
            $shortFilePath = $fileInfo->getRelativePathname();
            $bareFileName = str_replace('Spec.php', '', $shortFilePath);

            $testShortClassName = $bareFileName . 'Test';

            // has test case class in it?
            if (! str_contains($fileInfo->getContents(), 'class ' . $testShortClassName)) {
                continue;
            }

            // rename file
            $newFilePath = str_replace('Spec.php', 'Test.php', $fileInfo->getPathname());
            rename($fileInfo->getPathname(), $newFilePath);

            $symfonyStyle->note(sprintf('Renamed "%s" to %s"%s"', $fileInfo->getPathname(), PHP_EOL, $newFilePath));
        }

        return self::SUCCESS;
    }
}
