<?php

namespace Sets\Fixture;

use PhpSpec\ObjectBehavior;

class ShouldNotThrowExceptionSpec extends ObjectBehavior
{
    public function it_should_add_new_item()
    {
        $this->shouldNotThrow(\Exception::class)->during(
            'someMethodCall',
            ['someArguments']
        );
    }
}

?>
-----
<?php

namespace Sets\Fixture;

use PhpSpec\ObjectBehavior;

final class ShouldNotThrowExceptionTest extends \PHPUnit\Framework\TestCase
{
    public function testAddNewItem(): void
    {
        /** should not throw exception */
        $this->shouldNotThrowException->someMethodCall('someArguments');
    }
}

?>
