<?php

namespace Rector\PhpSpecToPHPUnit\Tests\Rector\Class_\LetToSetUpClassMethodRector\Fixture;

use PhpSpec\ObjectBehavior;

final class NoLetButNeeded extends ObjectBehavior
{
    public function it_does()
    {
    }
}

?>
-----
<?php

namespace Rector\PhpSpecToPHPUnit\Tests\Rector\Class_\LetToSetUpClassMethodRector\Fixture;

use PhpSpec\ObjectBehavior;

final class NoLetButNeeded extends ObjectBehavior
{
    protected function setUp(): void
    {
        parent::setUp();
        $this->noLetButNeeded = new \Rector\PhpSpecToPHPUnit\Tests\Rector\Class_\LetToSetUpClassMethodRector\Fixture\NoLetButNeeded();
    }
    public function it_does()
    {
    }
}

?>
