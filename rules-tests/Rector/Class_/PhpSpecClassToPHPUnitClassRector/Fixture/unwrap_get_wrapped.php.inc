<?php

namespace spec\Rector\PhpSpecToPHPUnit;

use PhpSpec\ObjectBehavior;

final class UnwrapGetWrapped extends ObjectBehavior
{
    public function test()
    {
        $wrappedObject = $this->getWrappedObject();
    }
}

?>
-----
<?php

namespace spec\Rector\PhpSpecToPHPUnit;

use PhpSpec\ObjectBehavior;

final class UnwrapGetWrapped extends ObjectBehavior
{
    public function test()
    {
        $wrappedObject = $this;
    }
}

?>
