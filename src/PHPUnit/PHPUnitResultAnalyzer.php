<?php

declare(strict_types=1);

namespace Rector\PhpSpecToPHPUnit\PHPUnit;

use Nette\Utils\FileSystem;
use Nette\Utils\Strings;
use Rector\PhpSpecToPHPUnit\ValueObject\PHPUnit\MoreThanOnceTestError;
use Rector\PhpSpecToPHPUnit\ValueObject\PHPUnit\NoCallTestError;
use Rector\PhpSpecToPHPUnit\ValueObject\PHPUnit\TestError;

final class PHPUnitResultAnalyzer
{
    /**
     * @var MoreThanOnceTestError[]
     */
    private $moreThanOnceTestErrors = [];

    /**
     * @var NoCallTestError[]
     */
    private $noCallTestErrors = [];

    /**
     * @return MoreThanOnceTestError[]
     */
    public function resolveMoreThanOnceTestErrors(string $phpunitResultFilePath): array
    {
        if ($this->moreThanOnceTestErrors !== []) {
            return $this->moreThanOnceTestErrors;
        }

        $testErrors = $this->matchTestErrors($phpunitResultFilePath);

        $this->moreThanOnceTestErrors = $this->matchMoreThanOnceTestErrors($testErrors);
        return $this->moreThanOnceTestErrors;
    }

    /**
     * @return NoCallTestError[]
     */
    public function resolveNoCallTestErrors(string $phpunitResultFilePath): array
    {
        $testErrors = $this->matchTestErrors($phpunitResultFilePath);

        if ($this->noCallTestErrors !== []) {
            return $this->noCallTestErrors;
        }

        $this->noCallTestErrors = $this->matchNoCallTestErrors($testErrors);
        return $this->noCallTestErrors;
    }

    /**
     * @return TestError[]
     */
    private function matchTestErrors(string $phpunitResultFilePath): array
    {
        $phpunitResultFileContents = FileSystem::read($phpunitResultFilePath);

        $testErrors = [];

        $matches = Strings::matchAll($phpunitResultFileContents, '#^(\d+\) .*?):\d+\n#ms');
        foreach ($matches as $match) {
            $testErrors[] = new TestError($match[1]);
        }

        return $testErrors;
    }

    /**
     * @param TestError[] $testErrors
     * @return MoreThanOnceTestError[]
     */
    private function matchMoreThanOnceTestErrors(array $testErrors): array
    {
        $moreThanOnceTestErrors = [];

        foreach ($testErrors as $testError) {
            $match = Strings::match(
                $testError->getErrorContents(),
                '#(?<test_class>[\w\\\\]+?)::(?<test_method_name>\w+)\n.*?(?<mocked_method_name>\w+)\(\).*?\s+was not expected to be called more than once#s'
            );

            if ($match === null) {
                continue;
            }

            $moreThanOnceTestErrors[] = new MoreThanOnceTestError(
                $match['test_class'],
                $match['test_method_name'],
                $match['mocked_method_name']
            );
        }

        return $moreThanOnceTestErrors;
    }

    /**
     * @param TestError[] $testErrors
     * @return NoCallTestError[]
     */
    private function matchNoCallTestErrors(array $testErrors): array
    {
        $noCallTestErrors = [];

        foreach ($testErrors as $testError) {
            $match = Strings::match(
                $testError->getErrorContents(),
                '#(?<test_class>[\w\\\\]+?)::(?<test_method_name>\w+)\s+Expectation failed for method name is "(?<mocked_method_name>.*?)" when .*?Method was expected to be called 1 times, actually called 0 times#s'
            );

            if ($match === null) {
                continue;
            }

            $noCallTestErrors[] = new NoCallTestError(
                $match['test_class'],
                $match['test_method_name'],
                $match['mocked_method_name']
            );
        }

        return $noCallTestErrors;
    }
}
