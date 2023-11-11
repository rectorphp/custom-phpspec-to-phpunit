<?php

namespace spec\Rector\PhpSpecToPHPUnit;

use PhpSpec\ObjectBehavior;

final class DuringWithNamedMethod extends ObjectBehavior
{
    public function is_shoud_be_valid()
    {
        $this->shouldThrow(ValidationException::class)->duringSomeMethod();
    }
}

?>
-----
<?php

namespace spec\Rector\PhpSpecToPHPUnit;

use PhpSpec\ObjectBehavior;

final class DuringWithNamedMethod extends ObjectBehavior
{
    public function is_shoud_be_valid()
    {
        $this->expectException(ValidationException::class);
        $this->someMethod();
    }
}

?>
