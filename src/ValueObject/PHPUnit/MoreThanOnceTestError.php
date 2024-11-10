<?php

declare(strict_types=1);

namespace Rector\PhpSpecToPHPUnit\ValueObject\PHPUnit;

final class MoreThanOnceTestError
{
    /**
     * @var string
     */
    private $testClass;
    /**
     * @var string
     */
    private $testClassMethod;
    /**
     * @var string
     */
    private $mockedMethod;
    public function __construct(string $testClass, string $testClassMethod, string $mockedMethod)
    {
        /**
         * @var class-string
         */
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
}
