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

    public function it_returns_id()
    {
        $this->id()->shouldReturn(5);
    }

    public function it_blows()
    {
        $this->shouldThrow('SomeException')->during('item', [5]);
    }

    public function it_should_be_called(Cart $cart)
    {
        $cart->price()->shouldBeCalled()->willReturn(5);
        $cart->shippingAddress(Argument::type(Address::class))->shouldBeCalled();
        $cart->shippingAddress(Argument::type('DateTime'))->shouldBeCalled();
    }

    public function is_bool_check()
    {
        $this->hasFailed()->shouldBe(false);
        $this->hasFailed()->shouldNotBe(false);
    }

    public function is_array_type()
    {
        $this->shippingAddresses()->shouldBeArray();
    }
}

?>
-----
<?php

namespace Tests\PhpSpecToPHPUnit\Fixture;

use PhpSpec\ObjectBehavior;
use Sets\Source\Address;
use Sets\Source\Cart;

class CreateMeTest extends \PHPUnit\Framework\TestCase
{
    private \PhpSpecToPHPUnit\Fixture\CreateMe $createMe;
    protected function setUp(): void
    {
        $this->createMe = new \PhpSpecToPHPUnit\Fixture\CreateMe(5);
    }

    public function testReturnsId(): void
    {
        $this->assertSame(5, $this->createMe->id());
    }

    public function testBlows(): void
    {
        $this->expectException('SomeException');
        $this->createMe->item(5);
    }

    public function testCalled(): void
    {
        /** @var Cart|\PHPUnit\Framework\MockObject\MockObject $cart */
        $cart = $this->createMock(Cart::class);
        $cart->expects($this->atLeastOnce())->method('price')->willReturn(5);
        $cart->expects($this->atLeastOnce())->method('shippingAddress')->with($this->isInstanceOf(Address::class));
        $cart->expects($this->atLeastOnce())->method('shippingAddress')->with($this->isInstanceOf('DateTime'));
    }

    public function testBoolCheck(): void
    {
        $this->assertFalse($this->createMe->hasFailed());
        $this->assertNotFalse($this->createMe->hasFailed());
    }

    public function testArrayType(): void
    {
        $this->assertIsIterable($this->createMe->shippingAddresses());
    }
}

?>
