<?php

namespace Rector\PhpSpecToPHPUnit\Tests\Rector\Class_\LetToSetUpClassMethodRector\Fixture;

use PhpSpec\ObjectBehavior;

final class LetGoLetMethods extends ObjectBehavior
{
    public function letGo()
    {
    }
}

?>
-----
<?php

namespace Rector\PhpSpecToPHPUnit\Tests\Rector\Class_\LetToSetUpClassMethodRector\Fixture;

use PhpSpec\ObjectBehavior;

final class LetGoLetMethods extends ObjectBehavior
{
    protected function tearDown(): void
    {
    }
}

?>
