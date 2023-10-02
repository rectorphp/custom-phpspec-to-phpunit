<?php

namespace spec\Rector\PhpSpecToPHPUnit;

use PhpSpec\ObjectBehavior;

final class SomeTypeCheck extends ObjectBehavior
{
    public function is_shoud_be_valid()
    {
        $this->shouldHaveType(SomeType::class);
    }
}

?>
-----
<?php

namespace spec\Rector\PhpSpecToPHPUnit;

use PhpSpec\ObjectBehavior;

final class SomeTypeCheck extends ObjectBehavior
{
}

?>
