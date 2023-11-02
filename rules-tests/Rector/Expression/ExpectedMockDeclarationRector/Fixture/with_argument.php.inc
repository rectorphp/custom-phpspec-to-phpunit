<?php

namespace Rector\PhpSpecToPHPUnit\Tests\Rector\Expression\ExpectedMockDeclarationRector\Fixture;

use PhpSpec\ObjectBehavior;
use Rector\PhpSpecToPHPUnit\Tests\Rector\Expression\ExpectedMockDeclarationRector\Source\SomeMethodType;

class WithArgumentSpec extends ObjectBehavior
{
    public function it_should_succeed()
    {
        $someMock = $this->createMock(SomeMethodType::class);
        $someMock->hasFailed('yes')->shouldBeCalled();
    }
}

?>
-----
<?php

namespace Rector\PhpSpecToPHPUnit\Tests\Rector\Expression\ExpectedMockDeclarationRector\Fixture;

use PhpSpec\ObjectBehavior;
use Rector\PhpSpecToPHPUnit\Tests\Rector\Expression\ExpectedMockDeclarationRector\Source\SomeMethodType;

class WithArgumentSpec extends ObjectBehavior
{
    public function it_should_succeed()
    {
        $someMock = $this->createMock(SomeMethodType::class);
        $someMock->expects($this->once())->method('hasFailed')->with('yes')->shouldBeCalled();
    }
}

?>
