<?php

namespace spec\PhpSpecToPHPUnit;

use PhpSpec\ObjectBehavior;
use Rector\PhpSpecToPHPUnit\Tests\Rector\ClassMethod\ConsecutiveMockExpectationRector\Source\MockedType;

class OnlyArgs extends ObjectBehavior
{
    public function it_should(MockedType $mockedType)
    {
        $mockedType->set('first_key', 'second_key')->shouldBeCalled();
        $mockedType->set('third_key', 'fourth_key')->shouldBeCalled();
    }
}

?>
    -----
<?php

namespace spec\PhpSpecToPHPUnit;

use PhpSpec\ObjectBehavior;
use Rector\PhpSpecToPHPUnit\Tests\Rector\ClassMethod\ConsecutiveMockExpectationRector\Source\MockedType;

class OnlyArgs extends ObjectBehavior
{
    public function it_should(MockedType $mockedType)
    {
        $mockedType->expects($this->exactly(2))->method('set')->willReturnMap([['first_key', 'second_key'], ['third_key', 'fourth_key']]);
    }
}

?>
    