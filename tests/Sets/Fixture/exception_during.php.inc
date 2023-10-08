<?php

namespace spec\PhpSpecToPHPUnit\Fixture;

use PhpSpec\ObjectBehavior;
use Sets\Source\Address;
use Sets\Source\Cart;

class ExceptionDuringSpec extends ObjectBehavior
{
    public function it_blows()
    {
        $this->shouldThrow('SomeException')->during('item', [5]);
    }
}

?>
-----
<?php

namespace Tests\PhpSpecToPHPUnit\Fixture;

use PhpSpec\ObjectBehavior;
use Sets\Source\Address;
use Sets\Source\Cart;

class ExceptionDuringTest extends \PHPUnit\Framework\TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        $this->exceptionDuring = new \PhpSpecToPHPUnit\Fixture\ExceptionDuring();
    }
    public function testBlows(): void
    {
        $this->expectException('SomeException');
        $this->exceptionDuring->item(5);
    }
}

?>
