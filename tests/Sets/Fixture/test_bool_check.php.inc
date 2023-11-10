<?php

namespace spec\PhpSpecToPHPUnit\Fixture;

use PhpSpec\ObjectBehavior;
use Sets\Source\Address;
use Sets\Source\Cart;

class TestBoolCheckSpec extends ObjectBehavior
{
    public function let()
    {
        $this->beConstructedWith(5);
    }

    public function is_bool_check()
    {
        $this->hasFailed()->shouldBe(false);
        $this->hasFailed()->shouldNotBe(false);
    }
}

?>
-----
<?php

namespace Tests\PhpSpecToPHPUnit\Fixture;

use PhpSpec\ObjectBehavior;
use Sets\Source\Address;
use Sets\Source\Cart;

final class TestBoolCheckTest extends \PHPUnit\Framework\TestCase
{
    private \PhpSpecToPHPUnit\Fixture\TestBoolCheck $testBoolCheck;
    protected function setUp(): void
    {
        $this->testBoolCheck = new \PhpSpecToPHPUnit\Fixture\TestBoolCheck(5);
    }

    public function testBoolCheck(): void
    {
        $this->assertFalse($this->testBoolCheck->hasFailed());
        $this->assertNotFalse($this->testBoolCheck->hasFailed());
    }
}

?>
