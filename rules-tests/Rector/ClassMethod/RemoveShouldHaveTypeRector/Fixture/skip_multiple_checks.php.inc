<?php

namespace spec\Rector\PhpSpecToPHPUnit;

use PhpSpec\ObjectBehavior;

final class SkipMultipleChecks extends ObjectBehavior
{
    public function is_shoud_be_valid()
    {
        $this->shouldHaveType(SomeType::class);
        $this->shouldReturn(100);
    }
}
