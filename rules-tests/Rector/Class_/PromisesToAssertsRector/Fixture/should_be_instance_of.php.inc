<?php

namespace spec\Rector\PhpSpecToPHPUnit;

use PhpSpec\ObjectBehavior;

class ShouldBeInstanceofMethod extends ObjectBehavior
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

class ShouldBeInstanceofMethod extends ObjectBehavior
{
    private \Rector\PhpSpecToPHPUnit\ShouldBeInstanceofMethod $shouldBeInstanceofMethod;
    protected function setUp(): void
    {
        $this->shouldBeInstanceofMethod = new \Rector\PhpSpecToPHPUnit\ShouldBeInstanceofMethod();
    }
    public function let_1()
    {
        $this->assertInstanceOf(\stdClass::class, $this->shouldBeInstanceofMethod);
    }

    public function let_2()
    {
        $this->assertInstanceOf(\stdClass::class, $this->shouldBeInstanceofMethod);
    }

    public function let_3()
    {
        $this->assertInstanceOf(\stdClass::class, $this->shouldBeInstanceofMethod);
    }

    public function let_4()
    {
        $this->assertInstanceOf(\stdClass::class, $this->shouldBeInstanceofMethod);
    }
}

?>
