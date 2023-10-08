<?php

namespace spec\Rector\PhpSpecToPHPUnit;

use PhpSpec\ObjectBehavior;

class TestClassMethodSpec extends ObjectBehavior
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

class TestClassMethodSpec extends ObjectBehavior
{
    private \Rector\PhpSpecToPHPUnit\TestClassMethod $testClassMethod;
    protected function setUp(): void
    {
        $this->testClassMethod = new \Rector\PhpSpecToPHPUnit\TestClassMethod(5);
    }
}

?>
