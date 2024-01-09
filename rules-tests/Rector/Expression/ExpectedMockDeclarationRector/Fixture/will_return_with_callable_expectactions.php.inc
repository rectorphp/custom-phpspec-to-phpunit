<?php

namespace Rector\PhpSpecToPHPUnit\Tests\Rector\Expression\ExpectedMockDeclarationRector\Fixture;

use PhpSpec\ObjectBehavior;
use Prophecy\Argument;

class WillReturnWithCallableExpectations extends ObjectBehavior
{
    public function it_should_succeed(SomeMailer $someMailer)
    {
        $someMailer->send(Argument::that(function (SomeClass $someClass) {
            return $someClass->getFirst() === 'first'
                && $someClass->getSecond() === 'second';
        }))->shouldBeCalled();
    }
}

?>
-----
<?php

namespace Rector\PhpSpecToPHPUnit\Tests\Rector\Expression\ExpectedMockDeclarationRector\Fixture;

use PhpSpec\ObjectBehavior;
use Prophecy\Argument;

class WillReturnWithCallableExpectations extends ObjectBehavior
{
    public function it_should_succeed(SomeMailer $someMailer)
    {
        $someMailer->expects($this->once())->method('send')->with($this->callable(function (SomeClass $someClass) {
            $this->assertSame('first', $someClass->getFirst());
            $this->assertSame('second', $someClass->getSecond());
            return true;
        }))->shouldBeCalled();
    }
}

?>
