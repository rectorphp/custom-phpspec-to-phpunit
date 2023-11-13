<?php

namespace Rector\PhpSpecToPHPUnit\Tests\Rector\Class_\ImplicitLetInitializationRector\Fixture;

use PhpSpec\ObjectBehavior;

final class SkipMultipleIfBeConstructed extends ObjectBehavior
{
    public function it_does()
    {
        $item = $this->beConstructedThrough('some');
        $this->shouldHaveType(Result::class);
    }

    public function it_does_again()
    {
        $item = $this->beConstructedThrough('some');
        $this->shouldHaveType(Result::class);
    }
}


