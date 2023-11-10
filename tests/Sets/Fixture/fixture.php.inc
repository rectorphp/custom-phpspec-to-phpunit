<?php

namespace spec\PhpSpecToPHPUnit\Fixture;

use PhpSpec\ObjectBehavior;
use Sets\Source\Address;
use Sets\Source\Cart;

class CreateMeSpec extends ObjectBehavior
{
    public function let()
    {
        $this->beConstructedWith(5);
    }

    public function it_should_be_called(Cart $cart)
    {
        $cart->price()->shouldBeCalled()->willReturn(5);

        $cart->shippingAddress(Argument::type(Address::class))->shouldBeCalled();
    }
}

?>
-----
<?php

namespace Tests\PhpSpecToPHPUnit\Fixture;

use PhpSpec\ObjectBehavior;
use Sets\Source\Address;
use Sets\Source\Cart;

final class CreateMeTest extends \PHPUnit\Framework\TestCase
{
    private \PhpSpecToPHPUnit\Fixture\CreateMe $createMe;
    protected function setUp(): void
    {
        $this->createMe = new \PhpSpecToPHPUnit\Fixture\CreateMe(5);
    }

    public function testCalled(): void
    {
        /** @var \Sets\Source\Cart|\PHPUnit\Framework\MockObject\MockObject $cartMock */
        $cartMock = $this->createMock(\Sets\Source\Cart::class);
        $cartMock->expects($this->once())->method('price')->willReturn(5);
        $cartMock->expects($this->once())->method('shippingAddress')->with($this->isInstanceOf(Address::class));
    }
}

?>
