<?php

declare(strict_types=1);

namespace Rector\PhpSpecToPHPUnit\Tests\Sets;

use Iterator;
use PHPUnit\Framework\Attributes\DataProvider;
use Rector\PhpSpecToPHPUnit\Set\MigrationSetList;
use Rector\Testing\PHPUnit\AbstractRectorTestCase;

final class PhpSpecToPHPUnitSetTest extends AbstractRectorTestCase
{
    #[DataProvider('provideData')]
    public function test(string $filePath): void
    {
        $this->doTestFile($filePath);
    }

    public static function provideData(): Iterator
    {
        return self::yieldFilesFromDirectory(__DIR__ . '/Fixture');
    }

    public function provideConfigFilePath(): string
    {
        return MigrationSetList::PHPSPEC_TO_PHPUNIT;
    }
}
