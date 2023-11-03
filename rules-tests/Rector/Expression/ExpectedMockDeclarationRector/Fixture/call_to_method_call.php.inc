<?php

namespace Rector\PhpSpecToPHPUnit\Tests\Rector\Expression\ExpectedMockDeclarationRector\Fixture;

use PhpSpec\ObjectBehavior;

class ResultSpec extends ObjectBehavior
{
    public function it_should_succeed()
    {
        $someMock = $this->createMock(SomeClass::class);
        $someMock->hasFailed()->shouldReturn(false);
    }
}

?>
-----
<?php

namespace Rector\PhpSpecToPHPUnit\Tests\Rector\Expression\ExpectedMockDeclarationRector\Fixture;

use PhpSpec\ObjectBehavior;

class ResultSpec extends ObjectBehavior
{
    public function it_should_succeed()
    {
        $someMock = $this->createMock(SomeClass::class);
        $someMock->expects($this->once())->method('hasFailed')->shouldReturn(false);
    }
}

?>
