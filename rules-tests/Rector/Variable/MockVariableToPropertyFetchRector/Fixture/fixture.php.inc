<?php

namespace spec\Rector\PhpSpecToPHPUnit\Tests\Rector\Variable\MockVariableToPropertyFetchRector\Fixture;

use PhpSpec\ObjectBehavior;

class CreateMeSpec extends ObjectBehavior
{
    public function it_returns_id()
    {
        $this->id()->shouldReturn(5);
    }

    public function is_bool_check()
    {
        $this->hasFailed()->shouldBe(false);
        $this->hasFailed()->shouldNotBe(false);
    }
}

?>
