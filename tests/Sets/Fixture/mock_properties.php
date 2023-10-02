<?php

namespace spec\Rector\PhpSpecToPHPUnit\Tests\Sets\Fixture;

use PhpSpec\ObjectBehavior;
use Sets\Source\OrderFactory;

class MockPropertiesSpec extends ObjectBehavior
{
    public function let(OrderFactory $factory)
    {
        $this->beConstructedWith($factory);
    }

    public function let_it_go(OrderFactory $factory)
    {
        $factory->someMethod()->shouldBeCalled();
        $this->run();
    }
}

?>
