<?php

namespace Sets\Fixture;

use PhpSpec\ObjectBehavior;

class ShouldReturnSpec extends ObjectBehavior
{
    public function let()
    {
        $this->beConstructedWith('some');
    }

    public function it_should_calculate_zero_price_when_cart_is_empty()
    {
        $price = $this->price();

        $price->shouldBeAnInstanceOf('someType');
        $price->withVat()->shouldReturn(0.0);
        $price->withoutVat()->shouldReturn(0.0);
        $price->vat()->shouldReturn(0.0);
    }
}

?>
-----
<?php

namespace Sets\Fixture;

use PhpSpec\ObjectBehavior;

class ShouldReturnTest extends \PHPUnit\Framework\TestCase
{
    private \Sets\Fixture\ShouldReturn $shouldReturn;
    protected function setUp(): void
    {
        $this->shouldReturn = new \Sets\Fixture\ShouldReturn('some');
    }

    public function testCalculateZeroPriceWhenCartIsEmpty(): void
    {
        $price = $this->shouldReturn->price();

        $this->assertInstanceOf('someType', $price);
        $this->assertSame(0.0, $price->withVat());
        $this->assertSame(0.0, $price->withoutVat());
        $this->assertSame(0.0, $price->vat());
    }
}

?>
