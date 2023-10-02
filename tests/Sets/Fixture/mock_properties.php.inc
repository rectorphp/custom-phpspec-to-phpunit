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

namespace Tests\Rector\PhpSpecToPHPUnit\Tests\Sets\Fixture;

use PhpSpec\ObjectBehavior;
use Sets\Source\OrderFactory;

class MockPropertiesTest extends \PHPUnit\Framework\TestCase
{
    private \Rector\PhpSpecToPHPUnit\Tests\Sets\Fixture\MockProperties $mockProperties;
    protected function setUp(): void
    {
        /** @var OrderFactory|\PHPUnit\Framework\MockObject\MockObject $factory */
        $factory = $this->createMock(OrderFactory::class);
        $this->mockProperties = new \Rector\PhpSpecToPHPUnit\Tests\Sets\Fixture\MockProperties($factory);
    }

    public function testLetItGo()
    {
        /** @var OrderFactory|\PHPUnit\Framework\MockObject\MockObject $factory */
        $factory = $this->createMock(OrderFactory::class);
        $factory->expects($this->atLeastOnce())->method('someMethod');
        $this->mockProperties->run();
    }
}

?>
