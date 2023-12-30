<?php

namespace Rector\PhpSpecToPHPUnit\Tests\Rector\Expression\ExpectedMockDeclarationRector\Fixture;

use PhpSpec\ObjectBehavior;

class SkipLocalPrivateMethodCall extends ObjectBehavior
{
    public function it_should_succeed()
    {
        $this->someLocalCall('first', 'second');
    }

    private function someLocalCall()
    {
    }
}
