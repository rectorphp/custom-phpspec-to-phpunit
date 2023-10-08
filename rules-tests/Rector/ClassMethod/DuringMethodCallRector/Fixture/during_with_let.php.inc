<?php

namespace spec\Rector\PhpSpecToPHPUnit;

use PhpSpec\ObjectBehavior;

class DuringWIthLet extends ObjectBehavior
{
    public function let()
    {
    }

    public function is_shoud_be_valid()
    {
        $this->shouldThrow(ValidationException::class)->during('someMethod');
    }
}

?>
-----
<?php

namespace spec\Rector\PhpSpecToPHPUnit;

use PhpSpec\ObjectBehavior;

class DuringWIthLet extends ObjectBehavior
{
    public function let()
    {
    }

    public function is_shoud_be_valid()
    {
        $this->expectException(ValidationException::class);
        $this->someMethod();
    }
}

?>
