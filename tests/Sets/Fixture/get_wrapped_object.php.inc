<?php

namespace spec\Rector\PhpSpecToPHPUnit\Tests\Sets\Fixture;

use PhpSpec\ObjectBehavior;

class BlablaSpec extends ObjectBehavior
{
    public function it_is_me(SomeType $someType)
    {
        $assignMe = $someType->getWrappedObject();
    }
}

?>
-----
<?php

namespace Sets\Fixture;

use Tests\Rector\PhpSpecToPHPUnit\Tests\Rector\Variable\PhpSpecToPHPUnitRector\Fixture\SomeType;

class BlablaTest extends \PHPUnit\Framework\TestCase
{
    private \Sets\Fixture\Blabla $blabla;
    protected function setUp(): void
    {
        parent::setUp();
        $this->blabla = new \Sets\Fixture\Blabla();
    }
    public function testMe()
    {
        /** @var SomeType|\PHPUnit\Framework\MockObject\MockObject $someType */
        $someType = $this->createMock(SomeType::class);
        $assignMe = $someType;
    }
}

?>
