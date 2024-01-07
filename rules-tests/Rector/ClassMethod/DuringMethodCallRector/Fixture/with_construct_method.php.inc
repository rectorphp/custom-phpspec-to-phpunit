<?php

namespace spec\Rector\PhpSpecToPHPUnit;

use PhpSpec\ObjectBehavior;

class WithConstructMethod extends ObjectBehavior
{
    public function is_shoud_be_valid()
    {
        $this->shouldThrow(ValidationException::class)->during('__construct', ['some_argument']);
    }
}

?>
-----
<?php

namespace spec\Rector\PhpSpecToPHPUnit;

use PhpSpec\ObjectBehavior;

class WithConstructMethod extends ObjectBehavior
{
    public function is_shoud_be_valid()
    {
        $this->expectException(ValidationException::class);
        new \Rector\PhpSpecToPHPUnit\WithConstructMethod('some_argument');
    }
}

?>
