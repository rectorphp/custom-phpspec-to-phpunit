<?php

namespace spec\Set\Sylius;

use PhpSpec\ObjectBehavior;

final class SomeTestedClassSpec extends ObjectBehavior
{
    public function it_is_initializable(): void
    {
        $this->shouldHaveType(SomeTestedClass::class);
    }

    public function it_implements(): void
    {
        $this->shouldImplement(RandomInterface::class);
    }
}

?>
-----
<?php

namespace Tests\Set\Sylius;

use PhpSpec\ObjectBehavior;

final class SomeTestedClassTest extends \PHPUnit\Framework\TestCase
{
    private \Set\Sylius\SomeTestedClass $someTestedClass;
    protected function setUp(): void
    {
        $this->someTestedClass = new \Set\Sylius\SomeTestedClass();
    }
    public function testImplements(): void
    {
        $this->assertInstanceOf(RandomInterface::class, $this->someTestedClass);
    }
}

?>
