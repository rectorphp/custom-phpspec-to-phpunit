<?php

declare(strict_types=1);

namespace Rector\PhpSpecToPHPUnit;

use PhpParser\Comment\Doc;
use PHPUnit\Framework\MockObject\MockObject;
use Rector\PhpSpecToPHPUnit\ValueObject\ServiceMock;

final class DocFactory
{
    public static function createForMockAssign(ServiceMock $serviceMock): Doc
    {
        $comment = sprintf(
            "/** @var \%s|\%s $%s */",
            $serviceMock->getMockClassName(),
            MockObject::class,
            $serviceMock->getVariableName() . 'Mock'
        );

        return new Doc($comment);
    }

    public static function createForMockProperty(ServiceMock $serviceMock): Doc
    {
        $comment = sprintf(
            "/**%s * @var \%s|\%s%s */",
            PHP_EOL,
            $serviceMock->getMockClassName(),
            MockObject::class,
            PHP_EOL
        );

        return new Doc($comment);
    }
}
