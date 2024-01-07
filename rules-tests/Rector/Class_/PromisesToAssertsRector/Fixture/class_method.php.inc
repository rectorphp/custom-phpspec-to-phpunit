<?php

namespace spec\Rector\PhpSpecToPHPUnit;

use PhpSpec\ObjectBehavior;

class TestClassMethod extends ObjectBehavior
{
    public function let_some_other()
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
    public function let_some_other()
    {
        $this->testClassMethod = new \Rector\PhpSpecToPHPUnit\TestClassMethod(5);
    }
}

?>
