<?php

declare(strict_types=1);

namespace Rector\PhpSpecToPHPUnit\ValueObject\PHPUnit;

final class TestError
{
    /**
     * @readonly
     * @var string
     */
    private $errorContents;
    public function __construct(string $errorContents)
    {
        $this->errorContents = $errorContents;
    }

    public function getErrorContents(): string
    {
        return $this->errorContents;
    }
}
