<?php

namespace Rector\PhpSpecToPHPUnit\Tests\Rector\ClassMethod\MoveParameterMockRector\Fixture;

use PhpSpec\ObjectBehavior;
use Rector\PhpSpecToPHPUnit\Tests\Rector\ClassMethod\MoveParameterMockRector\Source\SomeType;

final class ReUseFromLet extends ObjectBehavior
{
    public function let(SomeType $someType)
    {
    }

    public function it_should_have_value(SomeType $someType)
    {
        echo $someType;
    }
}

?>
-----
<?php

namespace Rector\PhpSpecToPHPUnit\Tests\Rector\ClassMethod\MoveParameterMockRector\Fixture;

use PhpSpec\ObjectBehavior;
use Rector\PhpSpecToPHPUnit\Tests\Rector\ClassMethod\MoveParameterMockRector\Source\SomeType;

final class ReUseFromLet extends ObjectBehavior
{
    public function let(SomeType $someType)
    {
    }

    public function it_should_have_value()
    {
        echo $this->someTypeMock;
    }
}

?>
