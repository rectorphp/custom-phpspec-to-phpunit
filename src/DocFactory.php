<?php

declare(strict_types=1);

namespace Rector\PhpSpecToPHPUnit;

use PhpParser\Comment\Doc;
use PHPUnit\Framework\MockObject\MockObject;
use Rector\PhpSpecToPHPUnit\ValueObject\VariableMock;

final class DocFactory
{
    public static function createForProperty(VariableMock $variableMock): Doc
    {
        $comment = sprintf("/**\n * @var \%s|\%s\n */", $variableMock->getMockClassName(), MockObject::class);

        return new Doc($comment);
    }
}
