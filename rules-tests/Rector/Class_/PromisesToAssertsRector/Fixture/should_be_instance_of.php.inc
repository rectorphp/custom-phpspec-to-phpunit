<?php

namespace spec\Rector\PhpSpecToPHPUnit;

use PhpSpec\ObjectBehavior;

class TestClassMethod extends ObjectBehavior
{
    public function let_1()
    {
        $this->shouldBeAnInstanceOf(\stdClass::class);
    }

    public function let_2()
    {
        $this->shouldHaveType(\stdClass::class);
    }

    public function let_3()
    {
        $this->shouldReturnAnInstanceOf(\stdClass::class);
    }

    public function let_4()
    {
        $this->shouldImplement(\stdClass::class);
    }
}

?>
-----
<?php

namespace spec\Rector\PhpSpecToPHPUnit;

use PhpSpec\ObjectBehavior;

class TestClassMethod extends ObjectBehavior
{
    public function let_1()
    {
        $this->assertInstanceOf(\stdClass::class, $this->testClassMethod);
    }

    public function let_2()
    {
        $this->assertInstanceOf(\stdClass::class, $this->testClassMethod);
    }

    public function let_3()
    {
        $this->assertInstanceOf(\stdClass::class, $this->testClassMethod);
    }

    public function let_4()
    {
        $this->assertInstanceOf(\stdClass::class, $this->testClassMethod);
    }
}

?>
