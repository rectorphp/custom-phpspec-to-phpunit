<?php

namespace Rector\PhpSpecToPHPUnit\Tests\Rector\Class_\LetToSetUpClassMethodRector\Fixture;

use PhpSpec\ObjectBehavior;

final class JustOnce extends ObjectBehavior
{
    public function let(FirstItem $firstItem, SecondItem $secondItem)
    {
    }
}

?>
-----
<?php

namespace Rector\PhpSpecToPHPUnit\Tests\Rector\Class_\LetToSetUpClassMethodRector\Fixture;

use PhpSpec\ObjectBehavior;

final class JustOnce extends ObjectBehavior
{
    protected function setUp(FirstItem $firstItem, SecondItem $secondItem): void
    {
    }
}

?>
