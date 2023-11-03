<?php

namespace Rector\PhpSpecToPHPUnit\Tests\Rector\Expression\ExpectedMockDeclarationRector\Fixture;

use PhpSpec\ObjectBehavior;
use Rector\PhpSpecToPHPUnit\Tests\Rector\Expression\ExpectedMockDeclarationRector\Source\SomeMethodType;

class ResultSpec extends ObjectBehavior
{
    public function it_should_succeed()
    {
        $someMock = $this->createMock(SomeMethodType::class);
        $this->assertsame('one', $someMock->hasFailed());
    }
}
