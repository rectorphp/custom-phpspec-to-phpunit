<?php

namespace Sets\Fixture;

use PhpSpec\ObjectBehavior;

class WillThrow extends ObjectBehavior
{
    public function it_should_add_new_item($client)
    {
        $client->request('some')->willThrow(new RequestException());
    }
}

?>
-----
<?php

namespace Sets\Fixture;

use PhpSpec\ObjectBehavior;

final class WillThrowTest extends \PHPUnit\Framework\TestCase
{
    public function testAddNewItem($client): void
    {
        $client->expects($this->once())->method('request')->with('some')->willThrowException(new RequestException());
    }
}

?>
