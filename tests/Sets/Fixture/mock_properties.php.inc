<?php

namespace spec\Rector\PhpSpecToPHPUnit\Tests\Sets\Fixture;

use PhpSpec\ObjectBehavior;
use Sets\Source\OrderFactory;

class MockPropertiesSpec extends ObjectBehavior
{
    public function let(OrderFactory $factory)
    {
        $this->beConstructedWith($factory);
    }

    public function let_it_go(OrderFactory $factory)
    {
        $factory->someMethod()->shouldBeCalled();
        $this->run();
    }
}

?>
-----
<?php

namespace Sets\Fixture;

use Sets\Source\OrderFactory;

class MockPropertiesTest extends \PHPUnit\Framework\TestCase
{
    private \Sets\Fixture\MockProperties $mockProperties;
    private \PHPUnit\Framework\MockObject\MockObject|\Sets\Source\OrderFactory $factory;
    protected function setUp(): void
    {
        $this->factory = $this->createMock(OrderFactory::class);
        $this->mockProperties = new \Sets\Fixture\MockProperties($this->factory);
    }

    public function testLetItGo()
    {
        $this->factory->expects($this->atLeastOnce())->method('someMethod');
        $this->mockProperties->run();
    }
}

?>
