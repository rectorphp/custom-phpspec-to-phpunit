<?php

declare(strict_types=1);

namespace Rector\PhpSpecToPHPUnit\ValueObject\PHPUnit;

final class MoreThanOnceTestError
{
    public function __construct(
        /**
         * @var class-string
         */
        private string $testClass,
        private string $testClassMethod,
        private string $mockedMethod
    ) {
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
}
