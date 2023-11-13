<?php

namespace Rector\PhpSpecToPHPUnit\Tests\Rector\Expression\ExpectedMockDeclarationRector\Fixture;

use PhpSpec\ObjectBehavior;
use Prophecy\Argument;

class ResultSpec extends ObjectBehavior
{
    public function it_should_succeed(ProducerInterface $producer)
    {
        $producer->publish(Argument::that(function () {
            return 1000;
        }))->shouldBeCalled();
    }
}

?>
-----
<?php

namespace Rector\PhpSpecToPHPUnit\Tests\Rector\Expression\ExpectedMockDeclarationRector\Fixture;

use PhpSpec\ObjectBehavior;
use Prophecy\Argument;

class ResultSpec extends ObjectBehavior
{
    public function it_should_succeed(ProducerInterface $producer)
    {
        $producer->expects($this->once())->method('publish')->willReturn(function () {
            return 1000;
        })->shouldBeCalled();
    }
}

?>
