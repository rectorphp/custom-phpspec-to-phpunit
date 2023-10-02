<?php

namespace Rector\PhpSpecToPHPUnit\Tests\Rector\Class_\LetToSetUpClassMethodRector\Fixture;

use PhpSpec\ObjectBehavior;

final class NoLetButNeeded extends ObjectBehavior
{
}

?>
-----
<?php

namespace Rector\PhpSpecToPHPUnit\Tests\Rector\Class_\LetToSetUpClassMethodRector\Fixture;

use PhpSpec\ObjectBehavior;

final class NoLetButNeeded extends ObjectBehavior
{
    private \Rector\PhpSpecToPHPUnit\Tests\Rector\Class_\LetToSetUpClassMethodRector\Fixture\JustOnce $justOnce;
}

?>
