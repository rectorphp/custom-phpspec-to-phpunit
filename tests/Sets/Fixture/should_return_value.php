<?php

namespace Sets\Fixture;

use PhpSpec\ObjectBehavior;

final class ShouldReturnTest extends \PHPUnit\Framework\TestCase
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
