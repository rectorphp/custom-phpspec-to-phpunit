<?php

namespace Rector\PhpSpecToPHPUnit\Tests\Rector\Class_\ImplicitLetInitializationRector\Fixture;

use PhpSpec\ObjectBehavior;

final class SkipWithLet extends ObjectBehavior
{
    private \stdClass $someItem;

    public function let()
    {
        $this->someItem = new \stdClass();
    }
}
