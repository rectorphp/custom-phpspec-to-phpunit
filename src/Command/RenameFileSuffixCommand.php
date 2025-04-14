<?php

declare(strict_types=1);

namespace Rector\PhpSpecToPHPUnit\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\Finder\Finder;
use Symfony\Component\Finder\SplFileInfo;

final class RenameFileSuffixCommand extends Command
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

        $specSuffixFileFinder = $this->findSpecSuffixFilesInDirectory($source);

        $symfonyStyle->title(sprintf('Found %d "*Spec.php" files', count($specSuffixFileFinder)));

        foreach ($specSuffixFileFinder as $fileInfo) {
            // get short file name without suffix
            /** @var SplFileInfo $fileInfo */
            $shortFilePath = $fileInfo->getBasename('.php');
            $bareFileName = str_replace('Spec', '', $shortFilePath);

            $testShortClassName = $bareFileName . 'Test';

            // has test case class in it?
            if (strpos($fileInfo->getContents(), 'class ' . $testShortClassName) === false) {
                continue;
            }

            // rename file
            $newFilePath = str_replace('Spec.php', 'Test.php', $fileInfo->getPathname());
            rename($fileInfo->getPathname(), $newFilePath);

            $symfonyStyle->note(sprintf('Renamed "%s" to %s"%s"', $fileInfo->getPathname(), PHP_EOL, $newFilePath));
        }

        return self::SUCCESS;
    }

    /**
     * @return Finder<SplFileInfo>
     */
    private function findSpecSuffixFilesInDirectory(string $source): Finder
    {
        return Finder::create()
            ->files()
            ->name('*Spec.php')
            ->in($source);
    }
}
