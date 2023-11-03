<?php

declare(strict_types=1);

namespace Rector\PhpSpecToPHPUnit;

use PhpParser\Comment\Doc;
use PHPUnit\Framework\MockObject\MockObject;
use Rector\PhpSpecToPHPUnit\ValueObject\ServiceMock;

final class DocFactory
{
    public static function createForMockAssign(ServiceMock $variableMock): Doc
    {
        $comment = sprintf(
            "/** @var \%s|\%s $%s */",
            $variableMock->getMockClassName(),
            MockObject::class,
            $variableMock->getVariableName() . 'Mock'
        );

        return new Doc($comment);
    }
}
