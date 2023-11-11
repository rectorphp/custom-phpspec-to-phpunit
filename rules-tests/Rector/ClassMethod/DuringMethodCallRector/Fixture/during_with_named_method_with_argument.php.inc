<?php

namespace spec\Rector\PhpSpecToPHPUnit;

use PhpSpec\ObjectBehavior;

final class DuringWithNamedMethodWithArgument extends ObjectBehavior
{
    public function is_shoud_be_valid()
    {
        $this->shouldThrow(ValidationException::class)->duringSomeMethod(1000);
    }
}

?>
-----
<?php

namespace spec\Rector\PhpSpecToPHPUnit;

use PhpSpec\ObjectBehavior;

final class DuringWithNamedMethodWithArgument extends ObjectBehavior
{
    public function is_shoud_be_valid()
    {
        $this->expectException(ValidationException::class);
        $this->someMethod(1000);
    }
}

?>
