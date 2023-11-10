<?php

namespace Rector\PhpSpecToPHPUnit\Tests\Rector\MethodCall\WithArgumentsMethodCallRector\Fixture;

use PhpSpec\ObjectBehavior;
use Prophecy\Argument;

class WillReturnEmptySpec extends ObjectBehavior
{
    public function it_is_initializable()
    {
        $this->run()->with(Argument::cetera());
    }
}

?>
-----
<?php

namespace Rector\PhpSpecToPHPUnit\Tests\Rector\MethodCall\WithArgumentsMethodCallRector\Fixture;

use PhpSpec\ObjectBehavior;
use Prophecy\Argument;

class WillReturnEmptySpec extends ObjectBehavior
{
    public function it_is_initializable()
    {
        $this->run()->with($this->any());
    }
}

?>
