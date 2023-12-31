<?php

namespace Rector\PhpSpecToPHPUnit\Tests\Rector\Expression\ShouldNeverBeCalledRector\Fixture;

use PhpSpec\ObjectBehavior;
use Prophecy\Argument;
use spec\PhpSpecToPHPUnit\Fixture\SerializerInterface;

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

namespace Rector\PhpSpecToPHPUnit\Tests\Rector\Expression\ShouldNeverBeCalledRector\Fixture;

use PhpSpec\ObjectBehavior;
use Prophecy\Argument;
use spec\PhpSpecToPHPUnit\Fixture\SerializerInterface;

class ResultSpec extends ObjectBehavior
{
    public function it_is_initializable(SerializerInterface $serializer)
    {
        $serializer->expects($this->never())->deserialize(Argument::any())->shouldNotBeCalled();
    }
}

?>
