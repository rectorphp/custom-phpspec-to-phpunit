<?php

namespace spec\PhpSpecToPHPUnit;

use PhpSpec\ObjectBehavior;

class SomeDuringWithArgument extends ObjectBehavior
{
    public function it_should()
    {
        $id = Uuid::uuid1();

        $this->shouldThrow(NotFoundException::class)->during('item', [$id]);
    }
}

?>
-----
<?php

namespace spec\PhpSpecToPHPUnit;

use PhpSpec\ObjectBehavior;

class SomeDuringWithArgument extends ObjectBehavior
{
    public function it_should()
    {
        $id = Uuid::uuid1();
        $this->expectException(NotFoundException::class);
        $this->item($id);
    }
}

?>
