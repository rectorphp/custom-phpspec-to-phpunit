<?php

namespace spec\Rector\PhpSpecToPHPUnit;

use PhpSpec\ObjectBehavior;

final class AssertDuringNewInstance extends ObjectBehavior
{
    public function is_shoud_be_valid()
    {
        $this->shouldThrow(new ValidationException('some contents'))->during('someMethod');
    }
}

?>
-----
<?php

namespace spec\Rector\PhpSpecToPHPUnit;

use PhpSpec\ObjectBehavior;

final class AssertDuringNewInstance extends ObjectBehavior
{
    public function is_shoud_be_valid()
    {
        $this->expectException(ValidationException::class);
        $this->expectExceptionMessage('some contents');
        $this->someMethod();
    }
}

?>
