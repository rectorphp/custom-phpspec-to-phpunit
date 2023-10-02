<?php

namespace Rector\PhpSpecToPHPUnit\Tests\Rector\Class_\LetToSetUpClassMethodRector\Fixture;

use PhpSpec\ObjectBehavior;

final class SkipSetupIfBeConstructed extends ObjectBehavior
{
    public function it_does()
    {
        $item = $this->beConstructedThrough('some');
    }
}
