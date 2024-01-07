<?php

namespace spec\PhpSpecToPHPUnit\Fixture;

use PhpSpec\ObjectBehavior;
use Sets\Source\Address;
use Sets\Source\Cart;

class ShouldReturn extends ObjectBehavior
{
    public function it_returns_id()
    {
        $this->id()->shouldReturn(5);
    }
}

?>
-----
<?php

namespace Tests\PhpSpecToPHPUnit\Fixture;

use PhpSpec\ObjectBehavior;
use Sets\Source\Address;
use Sets\Source\Cart;

final class ShouldReturnTest extends \PHPUnit\Framework\TestCase
{
    public function testReturnsId(): void
    {
        $this->assertSame(5, $this->shouldReturn->id());
    }
}

?>
