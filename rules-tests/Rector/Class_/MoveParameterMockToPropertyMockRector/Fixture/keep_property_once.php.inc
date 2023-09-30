<?php

namespace Rector\PhpSpecToPHPUnit\Tests\Rector\Class_\MoveParameterMockToPropertyMockRector\Fixture;

use PhpSpec\ObjectBehavior;
use Rector\PhpSpecToPHPUnit\Tests\Rector\Class_\MoveParameterMockToPropertyMockRector\Source\SomeType;

final class KeepPropertyOnce extends ObjectBehavior
{
    public function it_should_have_value(SomeType $someType)
    {
    }

    public function it_should(SomeType $someType)
    {
    }
}

?>
-----
<?php

namespace Rector\PhpSpecToPHPUnit\Tests\Rector\Class_\MoveParameterMockToPropertyMockRector\Fixture;

use PhpSpec\ObjectBehavior;
use Rector\PhpSpecToPHPUnit\Tests\Rector\Class_\MoveParameterMockToPropertyMockRector\Source\SomeType;

final class KeepPropertyOnce extends ObjectBehavior
{
    private \Rector\PhpSpecToPHPUnit\Tests\Rector\Class_\MoveParameterMockToPropertyMockRector\Source\SomeType|\PHPUnit\Framework\MockObject\MockObject $someType;
    public function it_should_have_value()
    {
    }

    public function it_should()
    {
    }
}

?>
