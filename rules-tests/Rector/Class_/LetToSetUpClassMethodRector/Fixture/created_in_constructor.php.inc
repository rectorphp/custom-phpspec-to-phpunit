<?php

namespace spec\Rector\PhpSpecToPHPUnit;

use PhpSpec\ObjectBehavior;

class TestClassMethod extends ObjectBehavior
{
    public function let()
    {
        $this->beConstructedWith(5);
    }
}

?>
-----
<?php

namespace spec\Rector\PhpSpecToPHPUnit;

use PhpSpec\ObjectBehavior;

class TestClassMethod extends ObjectBehavior
{
    private TestClassMethod $testClassMethod;

    protected function setUp(): void
    {
        $this->testClassMethod = new \Rector\PhpSpecToPHPUnit\TestClassMethod(5);
    }
}

?>
