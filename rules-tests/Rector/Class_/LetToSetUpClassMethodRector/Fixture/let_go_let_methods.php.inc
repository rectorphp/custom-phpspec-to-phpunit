<?php

namespace Rector\PhpSpecToPHPUnit\Tests\Rector\Class_\LetToSetUpClassMethodRector\Fixture;

use PhpSpec\ObjectBehavior;

final class LetGoLetMethods extends ObjectBehavior
{
    public function let()
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
    private \Rector\PhpSpecToPHPUnit\Tests\Rector\Class_\LetToSetUpClassMethodRector\Fixture\LetGoLetMethods $letGoLetMethods;
    protected function setUp(): void
    {
    }
}

?>
