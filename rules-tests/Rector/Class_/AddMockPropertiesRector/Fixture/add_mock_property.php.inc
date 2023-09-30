<?php

namespace Rector\PhpSpecToPHPUnit\Tests\Rector\Class_\AddMockPropertiesRector\Fixture;

use PhpSpec\ObjectBehavior;

final class AddMockProperty extends ObjectBehavior
{
    public function let()
    {
        $this->beConstructedWith('value');
    }

    public function it_should_have_value()
    {
        $this->getValue()->shouldReturn('value');
    }
}

?>
-----
