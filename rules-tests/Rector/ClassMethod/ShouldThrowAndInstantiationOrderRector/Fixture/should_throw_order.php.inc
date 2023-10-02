<?php

namespace spec\Rector\PhpSpecToPHPUnit;

use PhpSpec\ObjectBehavior;

class ShouldThrowOrder extends ObjectBehavior
{
    public function is_shoud_be_valid()
    {
        $this->beConstructedThrough('create');
        $this->shouldThrow(ValidationException::class)->duringInstantiation();
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
        $this->beConstructedThrough('create');
    }
}

?>
