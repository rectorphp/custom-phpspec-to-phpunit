<?php

namespace spec\Rector\PhpSpecToPHPUnit;

use PhpSpec\ObjectBehavior;

class ShouldThrowOrder extends ObjectBehavior
{
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

class ShouldThrowOrder extends ObjectBehavior
{
    public function is_shoud_be_valid()
    {
        $this->expectException(ValidationException::class);
        $this->someMethod();
    }
}

?>
