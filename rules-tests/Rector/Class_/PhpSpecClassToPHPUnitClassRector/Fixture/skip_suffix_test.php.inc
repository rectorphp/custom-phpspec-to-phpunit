<?php

namespace spec\Rector\PhpSpecToPHPUnit;

use PhpSpec\ObjectBehavior;

class SkipSuffixTest extends ObjectBehavior
{
    public function let()
    {
        $this->beConstructedWith(5);
    }
}
