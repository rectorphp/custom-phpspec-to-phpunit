<?php

require __DIR__ . '/../vendor/autoload.php';

// fix at $this->once() to $this->atLeastOnce() based on PHPUnit output
// remove method mock that was never called

use Nette\Utils\FileSystem;
use Symfony\Component\Console\Application;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\ArgvInput;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Webmozart\Assert\Assert;

final class FixMocksCommand extends Command
{
    private SymfonyStyle $symfonyStyle;

    protected function configure(): void
    {
        $this->setName('fix-mocks');
        $this->setDescription('Fixes the mock where set only ->once() but called ->atLeastOnce()');

        $this->addArgument(
            'phpunit-report-file',
            InputArgument::REQUIRED,
            'The phpunit report file - generate with "vendor/bin/phpunit > phpunit-report.txt'
        );
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $this->symfonyStyle = new SymfonyStyle($input, $output);

        $phpunitReportFile = (string) $input->getArgument('phpunit-report-file');
        Assert::fileExists($phpunitReportFile);

        $phpunitReportFileContents = FileSystem::read($phpunitReportFile);

        // create a result object from text
        $reportedErrors = \Nette\Utils\Strings::matchAll($phpunitReportFileContents, '#^(\d+\) .*?):\d+\n#ms');

        // @todo turn factory for $reportedErrors into value objects
        // with all the reported error metadata out of the box

        $i = 1;

        foreach ($reportedErrors as $reportedError) {
            $reportedErrorContents = $reportedError[1];

            // 1. more than one calls case
            $moreCallsMatch = \Nette\Utils\Strings::match(
                $reportedErrorContents,
                '#(?<test_class>[\w\\\\]+?)::(?<test_method_name>\w+)\n.*?(?<mocked_method_name>\w+)\(\).*?\s+was not expected to be called more than once#s'
            );

            if ($moreCallsMatch !== null) {
                $testedMethod = new TestedMethod(
                    $moreCallsMatch['test_class'],
                    $moreCallsMatch['test_method_name'],
                    $moreCallsMatch['mocked_method_name']
                );

                $testFileContents = $testedMethod->getTestFileContents();

                // find test method in the test file contents
                $changedTestFileContents = \Nette\Utils\Strings::replace($testFileContents, $testedMethod->getTestMethodRegex(), function (
                    array $match
                ) use ($testedMethod, &$i): ?string {
                    $cleanedTestMethodLines = [];

                    $testMethodLines = explode(PHP_EOL, $match['test_method_contents']);

                    // find mocked method line
                    foreach ($testMethodLines as $key => $testMethodLine) {
                        // must be "once()"
                        if (! str_contains($testMethodLine, 'once()')) {
                            continue;
                        }

                        if (! str_contains($testMethodLine, $testedMethod->getMockedMethod())) {
                            continue;
                        }

                        // note
                        $this->symfonyStyle->note(
                            sprintf(
                                '%d) A $this->once() for "%s()" method mock in test method %s::%s() was update to $this->atLeast()',
                                $i,
                                $testedMethod->getMockedMethod(),
                                $testedMethod->getTestClass(),
                                $testedMethod->getTestClassMethod()
                            )
                        );

                        $testMethodLines[$key] = str_replace('$this->once()', '$this->atLeastOnce()', $testMethodLine);
                        ++$i;
                    }

                    $changedTestMethodContents = implode(PHP_EOL, $testMethodLines);
                    return $match['start'] . $changedTestMethodContents . $match['end'];
                });

                // update test contents
                FileSystem::write($testedMethod->getTestFilePath(), $changedTestFileContents);
            }

            // 1. no calls case
            $zeroCallsMatch = \Nette\Utils\Strings::match(
                $reportedErrorContents,
                '#(?<test_class>[\w\\\\]+?)::(?<test_method_name>\w+)\s+Expectation failed for method name is "(?<mocked_method_name>.*?)" when .*?Method was expected to be called 1 times, actually called 0 times#s'
            );

            if ($zeroCallsMatch !== null) {
                $testedMethod = new TestedMethod(
                    $zeroCallsMatch['test_class'],
                    $zeroCallsMatch['test_method_name'],
                    $zeroCallsMatch['mocked_method_name']
                );

                $testFileContents = $testedMethod->getTestFileContents();

                // find test method in the test file contents
                $changedTestFileContents = \Nette\Utils\Strings::replace($testFileContents, $testedMethod->getTestMethodRegex(), function (
                    array $match
                ) use ($testedMethod, &$i): ?string {
                    $cleanedTestMethodLines = [];

                    $testMethodLines = explode(PHP_EOL, $match['test_method_contents']);

                    // find mocked method line
                    foreach ($testMethodLines as $testMethodLine) {
                        if (str_contains($testMethodLine, $testedMethod->getMockedMethod())) {
                            continue;
                        }

                        // note
                        $this->symfonyStyle->note(
                            sprintf(
                                '%d) A line for unused "%s()" method mock in test method %s::%s() was removed',
                                $i,
                                $testedMethod->getMockedMethod(),
                                $testedMethod->getTestFilePath(),
                                $testedMethod->getTestClassMethod()
                            )
                        );

                        $cleanedTestMethodLines[] = $testMethodLine;
                        ++$i;
                    }

                    if (count($cleanedTestMethodLines) === count($testMethodLines)) {
                        // return original contents
                        return $match[0];
                    }

                    $changedTestMethodContents = implode(PHP_EOL, $cleanedTestMethodLines);
                    return $match['start'] . $changedTestMethodContents . $match['end'];
                });

                // update test contents
                FileSystem::write($testedMethod->getTestFilePath(), $changedTestFileContents);
            }
        }

        return self::SUCCESS;
    }
}

final class TestCaseAnalyzer
{
    public static function resolveFilePath(string $testCaseClassName): string
    {
        $testCaseReflectionClass = new ReflectionClass($testCaseClassName);
        return $testCaseReflectionClass->getFileName();
    }
}

final class TestedMethod
{
    private string $testClass;

    private string $testClassMethod;

    private string $mockedMethod;

    public function __construct(
        string $testClass,
        string $testClassMethod,
        string $mockedMethod
    ) {
        $this->testClass = $testClass;
        $this->testClassMethod = $testClassMethod;
        $this->mockedMethod = $mockedMethod;
    }

    public function getTestClass(): string
    {
        return $this->testClass;
    }

    public function getTestClassMethod(): string
    {
        return $this->testClassMethod;
    }

    public function getMockedMethod(): string
    {
        return $this->mockedMethod;
    }

    public function getTestFilePath(): string
    {
        return TestCaseAnalyzer::resolveFilePath($this->testClass);
    }

    public function getTestFileContents(): string
    {
        return FileSystem::read($this->getTestFilePath());
    }

    public function getTestMethodRegex(): string
    {
        return '#(?<start>public function\s+' . $this->testClassMethod . '\(\).*?\n    \{)(?<test_method_contents>.*?)(?<end>\n    \})#s';
    }
}

$application = new Application();
$application->add(new FixMocksCommand());
$application->get('completion')
    ->setHidden(true);
$application->get('help')
    ->setHidden(true);
$application->get('list')
    ->setHidden(true);

$argvInput = new ArgvInput();
exit($application->run($argvInput));
