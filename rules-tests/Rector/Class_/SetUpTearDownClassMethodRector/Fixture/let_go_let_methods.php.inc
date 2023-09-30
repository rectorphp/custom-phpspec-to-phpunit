<?php

namespace Rector\PhpSpecToPHPUnit\Tests\Rector\Class_\SetUpTearDownClassMethodRector\Fixture;

use PhpSpec\ObjectBehavior;

final class LetGoLetMethods extends ObjectBehavior
{
    public function let()
    {
    }

    public function letGo()
    {
    }
}

?>
-----
<?php

namespace Rector\PhpSpecToPHPUnit\Tests\Rector\Class_\SetUpTearDownClassMethodRector\Fixture;

use PhpSpec\ObjectBehavior;

final class LetGoLetMethods extends ObjectBehavior
{
    protected function setUp(): void
    {
    }

    protected function tearDown(): void
    {
    }
}

?>
