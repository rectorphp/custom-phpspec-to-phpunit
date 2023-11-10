<?php

namespace spec\Rector\PhpSpecToPHPUnit;

use PhpSpec\ObjectBehavior;

class DefaultClassWithSetupProperty extends ObjectBehavior
{
}

?>
-----
<?php

namespace spec\Rector\PhpSpecToPHPUnit;

use PhpSpec\ObjectBehavior;

final class DefaultClassWithSetupPropertyTest extends \PHPUnit\Framework\TestCase
{
}

?>
