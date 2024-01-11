<?php

declare(strict_types=1);

namespace Rector\PhpSpecToPHPUnit\ValueObject\PHPUnit;

final readonly class TestError
{
    public function __construct(
        private string $errorContents
    ) {
    }

    public function getErrorContents(): string
    {
        return $this->errorContents;
    }
}
