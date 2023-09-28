<?php

namespace spec\Rector\PhpSpecToPHPUnit\Tests\Rector\Variable\PhpSpecToPHPUnitRector\Fixture;

use PhpSpec\ObjectBehavior;
use Rector\PhpSpecToPHPUnit\Tests\Rector\Variable\PhpSpecToPHPUnitRector\Source\OrderFactory;

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

namespace Tests\Rector\PhpSpecToPHPUnit\Tests\Rector\Variable\PhpSpecToPHPUnitRector\Fixture;

use PhpSpec\ObjectBehavior;
use Rector\PhpSpecToPHPUnit\Tests\Rector\Variable\PhpSpecToPHPUnitRector\Source\OrderFactory;

class MockPropertiesTest extends \PHPUnit\Framework\TestCase
{
    private \Rector\PhpSpecToPHPUnit\Tests\Rector\Variable\PhpSpecToPHPUnitRector\Fixture\MockProperties $mockProperties;
    private \PHPUnit\Framework\MockObject\MockObject|\Rector\PhpSpecToPHPUnit\Tests\Rector\Variable\PhpSpecToPHPUnitRector\Source\OrderFactory $factory;
    protected function setUp()
    {
        $this->factory = $this->createMock(OrderFactory::class);
        $this->mockProperties = new \Rector\PhpSpecToPHPUnit\Tests\Rector\Variable\PhpSpecToPHPUnitRector\Fixture\MockProperties($this->factory);
    }

    public function testLetItGo()
    {
        $this->factory->expects($this->atLeastOnce())->method('someMethod');
        $this->mockProperties->run();
    }
}

?>
