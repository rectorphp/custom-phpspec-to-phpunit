<?php

namespace spec\PhpSpecToPHPUnit;

use PhpSpec\ObjectBehavior;
use Rector\PhpSpecToPHPUnit\Tests\Rector\ClassMethod\ConsecutiveMockExpectationRector\Source\MockedType;

class SkipDifferentVarialbeName extends ObjectBehavior
{
    public function it_should(MockedType $mockedType, MockedType $anotherMockedType)
    {
        $mockedType->set('first_key')->shouldReturn(100);
        $anotherMockedType->set('second_key')->shouldReturn(200);
    }
}
