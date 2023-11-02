<?php

namespace spec\PhpSpecToPHPUnit\Fixture;

use PhpSpec\ObjectBehavior;
use Rector\PhpSpecToPHPUnit\Tests\Rector\Class_\PhpSpecMocksToPHPUnitMocksRector\Source\MethodType;

class SkipMethodCall extends ObjectBehavior
{
    public function it_is_initializable()
    {
        $serializer = new MethodType();
        $serializer->deserialize();
    }
}
