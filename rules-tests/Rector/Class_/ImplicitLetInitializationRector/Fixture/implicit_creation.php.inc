<?php

namespace Rector\PhpSpecToPHPUnit\Tests\Rector\Class_\ImplicitLetInitializationRector\Fixture;

use PhpSpec\ObjectBehavior;

final class JustOnceSpec extends ObjectBehavior
{
    public function it_should()
    {
        $this->modify();
    }
}

?>
-----
<?php

namespace Rector\PhpSpecToPHPUnit\Tests\Rector\Class_\ImplicitLetInitializationRector\Fixture;

use PhpSpec\ObjectBehavior;

final class JustOnceSpec extends ObjectBehavior
{
    private \Rector\PhpSpecToPHPUnit\Tests\Rector\Class_\ImplicitLetInitializationRector\Fixture\JustOnce $justOnce;
    protected function setUp(): void
    {
        $this->justOnce = new \Rector\PhpSpecToPHPUnit\Tests\Rector\Class_\ImplicitLetInitializationRector\Fixture\JustOnce();
    }
    public function it_should()
    {
        $this->modify();
    }
}

?>
