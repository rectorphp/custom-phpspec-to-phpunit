<?php

namespace spec\Rector\PhpSpecToPHPUnit;

use PhpSpec\ObjectBehavior;

class UpdateNamespace extends ObjectBehavior
{
}

?>
-----
<?php

namespace Tests\Rector\PhpSpecToPHPUnit;

use PhpSpec\ObjectBehavior;

class UpdateNamespace extends ObjectBehavior
{
}

?>
