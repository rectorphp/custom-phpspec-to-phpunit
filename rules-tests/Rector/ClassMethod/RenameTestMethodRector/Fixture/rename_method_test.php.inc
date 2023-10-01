<?php

namespace spec\Rector\PhpSpecToPHPUnit;

use PhpSpec\ObjectBehavior;

class RenameMethodTest extends ObjectBehavior
{
    public function is_shoud_be_valid()
    {
    }
}

?>
-----
<?php

namespace spec\Rector\PhpSpecToPHPUnit;

use PhpSpec\ObjectBehavior;

class RenameMethodTest extends ObjectBehavior
{
    public function testShoudBeValid()
    {
    }
}

?>
