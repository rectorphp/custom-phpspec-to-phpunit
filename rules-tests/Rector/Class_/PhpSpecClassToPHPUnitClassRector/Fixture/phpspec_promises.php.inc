<?php

namespace Rector\PhpSpecToPHPUnit\Tests\Rector\Class_\PhpSpecPromisesToPHPUnitAssertRector\Fixture;

use PhpSpec\ObjectBehavior;

final class PhpSpecPromises extends ObjectBehavior
{
    public function it_returns_id()
    {
        $this->id()->shouldReturn(5);
    }
}

?>
-----
<?php

namespace Rector\PhpSpecToPHPUnit\Tests\Rector\Class_\PhpSpecPromisesToPHPUnitAssertRector\Fixture;

use PhpSpec\ObjectBehavior;

final class PhpSpecPromises extends ObjectBehavior
{
    public function it_returns_id()
    {
        $this->assertSame(5, $this->phpSpecPromises->id());
    }
}

?>
