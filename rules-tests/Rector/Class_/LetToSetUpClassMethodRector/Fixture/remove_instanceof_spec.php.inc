<?php

namespace spec\Rector\PhpSpecToPHPUnit;

use PhpSpec\ObjectBehavior;

class RemoveInstanceofSpec extends ObjectBehavior
{
    public function let()
    {
        $this->beAnInstanceOf(TestClassMethod::class);
        $this->beConstructedWith(5);
    }
}

?>
-----
<?php

namespace spec\Rector\PhpSpecToPHPUnit;

use PhpSpec\ObjectBehavior;

class RemoveInstanceofSpec extends ObjectBehavior
{
    private \Rector\PhpSpecToPHPUnit\RemoveInstanceof $removeInstanceof;
    protected function setUp(): void
    {
        $this->removeInstanceof = new \Rector\PhpSpecToPHPUnit\RemoveInstanceof(5);
    }
}

?>
