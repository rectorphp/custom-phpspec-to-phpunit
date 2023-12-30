<?php

namespace spec\PhpSpecToPHPUnit;

use PhpSpec\ObjectBehavior;
use Rector\PhpSpecToPHPUnit\Tests\Rector\ClassMethod\ConsecutiveMockExpectationRector\Source\MockedType;

class TwiceCalled extends ObjectBehavior
{
    public function it_should(MockedType $mockedType)
    {
        $mockedType->set('first_key', 100)->shouldBeCalled();
        $mockedType->set('second_key', 200)->shouldBeCalled();
    }
}

?>
-----
<?php

namespace spec\PhpSpecToPHPUnit;

use PhpSpec\ObjectBehavior;

class TwiceCalled extends ObjectBehavior
{
    public function it_should(MockedType $mockedType)
    {
        $mockedType->expects($this->exactly(2))->method('set')
            ->willReturnMap([
                ['first_key', 100],
                ['second_key', 200],
            ]);
    }
}

?>
