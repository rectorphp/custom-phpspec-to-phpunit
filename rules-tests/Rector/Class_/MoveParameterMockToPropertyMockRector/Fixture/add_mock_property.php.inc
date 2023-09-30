<?php

namespace Rector\PhpSpecToPHPUnit\Tests\Rector\Class_\MoveParameterMockToPropertyMockRector\Fixture;

use PhpSpec\ObjectBehavior;
use Rector\PhpSpecToPHPUnit\Tests\Rector\Class_\MoveParameterMockToPropertyMockRector\Source\SomeType;

final class AddMockProperty extends ObjectBehavior
{
    public function let(SomeType $someType)
    {
    }

    public function it_should_have_value(SomeType $someType)
    {
    }
}

?>
-----
<?php

namespace Rector\PhpSpecToPHPUnit\Tests\Rector\Class_\MoveParameterMockToPropertyMockRector\Fixture;

use PhpSpec\ObjectBehavior;
use Rector\PhpSpecToPHPUnit\Tests\Rector\Class_\MoveParameterMockToPropertyMockRector\Source\SomeType;

final class AddMockProperty extends ObjectBehavior
{
    private \Rector\PhpSpecToPHPUnit\Tests\Rector\Class_\MoveParameterMockToPropertyMockRector\Source\SomeType|\PHPUnit\Framework\MockObject\MockObject $someType;
    public function let(SomeType $someType)
    {
    }

    public function it_should_have_value()
    {
    }
}

?>
