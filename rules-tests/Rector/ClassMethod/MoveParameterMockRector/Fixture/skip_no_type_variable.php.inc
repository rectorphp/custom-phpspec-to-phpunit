<?php

namespace Rector\PhpSpecToPHPUnit\Tests\Rector\ClassMethod\MoveParameterMockRector\Fixture;

use PhpSpec\ObjectBehavior;
use Rector\PhpSpecToPHPUnit\Tests\Rector\ClassMethod\MoveParameterMockRector\Source\SomeType;

final class SkipNoTypeVariable extends ObjectBehavior
{
    public function it_should_have_value($variable)
    {
        echo $variable;
    }
}
