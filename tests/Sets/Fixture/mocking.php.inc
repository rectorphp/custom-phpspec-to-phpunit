<?php

namespace spec\Rector\PhpSpecToPHPUnit\Tests\Sets\Fixture;

use PhpSpec\ObjectBehavior;
use Sets\Source\OrderFactory;
use Sets\Source\ShippingMethod;

class OrderSpec extends ObjectBehavior
{
    public function let(OrderFactory $factory, ShippingMethod $shippingMethod)
    {
        $factory->createShippingMethodFor(Argument::any())->shouldBeCalled()->willReturn($shippingMethod);
        $factory->anotherMethod(25)->shouldBeCalled()->willReturn($shippingMethod);
    }
}

?>
-----
<?php

namespace Sets\Fixture;

use Tests\Rector\PhpSpecToPHPUnit\Tests\Rector\Variable\PhpSpecToPHPUnitRector\Fixture\OrderFactory;
use Tests\Rector\PhpSpecToPHPUnit\Tests\Rector\Variable\PhpSpecToPHPUnitRector\Fixture\ShippingMethod;

class OrderTest extends \PHPUnit\Framework\TestCase
{
    private \Rector\PhpSpecToPHPUnit\Tests\Sets\Fixture\Order $order;
    protected function setUp(): void
    {
        /** @var OrderFactory|\PHPUnit\Framework\MockObject\MockObject $factory */
        $factory = $this->createMock(OrderFactory::class);
        /** @var ShippingMethod|\PHPUnit\Framework\MockObject\MockObject $shippingMethod */
        $shippingMethod = $this->createMock(ShippingMethod::class);
        $factory->expects($this->atLeastOnce())->method('createShippingMethodFor')->willReturn($shippingMethod);
        $factory->expects($this->atLeastOnce())->method('anotherMethod')->with($this->equalTo(25))->willReturn($shippingMethod);
    }
}

?>
