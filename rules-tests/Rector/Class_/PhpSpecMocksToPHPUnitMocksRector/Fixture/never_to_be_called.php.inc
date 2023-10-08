<?php

namespace spec\PhpSpecToPHPUnit\Fixture;

use PhpSpec\ObjectBehavior;
use Prophecy\Argument;

class ResultSpec extends ObjectBehavior
{
    public function it_is_initializable(SerializerInterface $serializer)
    {
        $serializer->deserialize(Argument::any())->shouldNotBeCalled();
    }
}

?>
-----
<?php

namespace spec\PhpSpecToPHPUnit\Fixture;

use PhpSpec\ObjectBehavior;
use Prophecy\Argument;

class ResultSpec extends ObjectBehavior
{
    public function it_is_initializable()
    {
        /** @var SerializerInterface|\PHPUnit\Framework\MockObject\MockObject $serializer */
        $serializer = $this->createMock(SerializerInterface::class);
        $serializer->method('deserialize')->expects($this->never());
    }
}

?>
