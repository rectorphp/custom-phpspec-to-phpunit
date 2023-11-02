<?php

namespace Rector\PhpSpecToPHPUnit\Tests\Rector\Expression\ExpectedMockDeclarationRector\Fixture;

use PhpSpec\ObjectBehavior;
use Rector\PhpSpecToPHPUnit\Tests\Rector\Expression\ExpectedMockDeclarationRector\Source\SomeMethodType;

class SkipMethodCall extends ObjectBehavior
{
    public function it_is_initializable()
    {
        $serializer = new SomeMethodType();
        $serializer->deserialize();
    }
}
