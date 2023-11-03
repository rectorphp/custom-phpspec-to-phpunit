<?php

namespace Rector\PhpSpecToPHPUnit\Tests\Rector\ClassMethod\MoveParameterMockRector\Fixture;

use PhpSpec\ObjectBehavior;
use Rector\PhpSpecToPHPUnit\Tests\Rector\ClassMethod\MoveParameterMockRector\Source\SomeType;

final class AddMockProperty extends ObjectBehavior
{
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

final class AddMockProperty extends ObjectBehavior
{
    public function it_should_have_value()
    {
        /** @var \Rector\PhpSpecToPHPUnit\Tests\Rector\ClassMethod\MoveParameterMockRector\Source\SomeType|\PHPUnit\Framework\MockObject\MockObject $someTypeMock */
        $someTypeMock = $this->createMock(\Rector\PhpSpecToPHPUnit\Tests\Rector\ClassMethod\MoveParameterMockRector\Source\SomeType::class);
        echo $someTypeMock;
    }
}

?>
