<?php

namespace Sets\Fixture;

use PhpSpec\ObjectBehavior;
use Sets\Source\AnotherMock;
use spec\Rector\PhpSpecToPHPUnit\Tests\Rector\Variable\PhpSpecToPHPUnitRector\Fixture\OrderFactory;

class MockPropertiesSpec extends ObjectBehavior
{
    public function let(OrderFactory $factory)
    {
        $this->beConstructedWith($factory);
    }

    public function let_it_go(AnotherMock $anotherMock)
    {
        $anotherMock->setName('Nummy');
        $this->addAnotherMock($anotherMock);
    }

    public function let_it_go_again(AnotherMock $anotherMock)
    {
        $anotherMock->setName('Nummy2');
        $this->addAnotherMock($anotherMock);
    }
}

?>
-----
<?php

namespace Sets\Fixture;

use PhpSpec\ObjectBehavior;
use Sets\Source\AnotherMock;
use spec\Rector\PhpSpecToPHPUnit\Tests\Rector\Variable\PhpSpecToPHPUnitRector\Fixture\OrderFactory;

class MockPropertiesTest extends \PHPUnit\Framework\TestCase
{
    private \Sets\Fixture\MockProperties $mockProperties;
    protected function setUp(): void
    {
        /** @var OrderFactory|\PHPUnit\Framework\MockObject\MockObject $factory */
        $factory = $this->createMock(OrderFactory::class);
        $this->mockProperties = new \Sets\Fixture\MockProperties($factory);
    }

    public function testLetItGo(): void
    {
        /** @var AnotherMock|\PHPUnit\Framework\MockObject\MockObject $anotherMock */
        $anotherMock = $this->createMock(AnotherMock::class);
        $anotherMock->setName('Nummy');
        $this->mockProperties->addAnotherMock($anotherMock);
    }

    public function testLetItGoAgain(): void
    {
        /** @var AnotherMock|\PHPUnit\Framework\MockObject\MockObject $anotherMock */
        $anotherMock = $this->createMock(AnotherMock::class);
        $anotherMock->setName('Nummy2');
        $this->mockProperties->addAnotherMock($anotherMock);
    }
}

?>
