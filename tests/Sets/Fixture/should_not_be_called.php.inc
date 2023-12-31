<?php

namespace spec\PhpSpecToPHPUnit\Fixture;

use PhpSpec\ObjectBehavior;
use Sets\Source\Address;
use Sets\Source\Cart;

class ShouldNotBeCalled extends ObjectBehavior
{
    public function it_should_be_called(Cart $cart)
    {
        $cart->price()->shouldNotBeCalled();
    }
}

?>
-----
<?php

namespace Tests\PhpSpecToPHPUnit\Fixture;

use PhpSpec\ObjectBehavior;
use Sets\Source\Address;
use Sets\Source\Cart;

final class ShouldNotBeCalledTest extends \PHPUnit\Framework\TestCase
{
    public function testCalled(): void
    {
        /** @var \Sets\Source\Cart|\PHPUnit\Framework\MockObject\MockObject $cartMock */
        $cartMock = $this->createMock(\Sets\Source\Cart::class);
        $cartMock->expects($this->never())->method('price');
    }
}

?>
