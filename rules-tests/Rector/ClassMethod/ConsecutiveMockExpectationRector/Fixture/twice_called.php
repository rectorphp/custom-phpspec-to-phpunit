<?php

namespace spec\PhpSpecToPHPUnit;

use PhpSpec\ObjectBehavior;
use Rector\PhpSpecToPHPUnit\Tests\Rector\ClassMethod\ConsecutiveMockExpectationRector\Source\MockedType;

class TwiceCalled extends ObjectBehavior
{
    public function is_should(MockedType $mockedType)
    {
        $mockedType->set('first_key', 100)->shouldBeCalled();
        $mockedType->set('second_key', 200)->shouldBeCalled();
    }
}

?>
