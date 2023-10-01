<?php

namespace spec\Rector\PhpSpecToPHPUnit;

use PhpSpec\ObjectBehavior;

class DefaultClassWithSetupProperty extends ObjectBehavior
{
    public function let()
    {
        $this->beConstructedWith(5);
    }
}

?>
-----
<?php

namespace spec\Rector\PhpSpecToPHPUnit;

use PhpSpec\ObjectBehavior;

class DefaultClassWithSetupPropertyTest extends \PHPUnit\Framework\TestCase
{
    private \Rector\PhpSpecToPHPUnit\DefaultClassWithSetupProperty $defaultClassWithSetupProperty;
    public function let()
    {
        $this->beConstructedWith(5);
    }
}

?>
