<?php

namespace Rector\PhpSpecToPHPUnit\Tests\Sets\Fixture;

use PhpSpec\ObjectBehavior;

class KeepLocal extends ObjectBehavior
{
    public function it_should_succeed()
    {
        $this->someLocalCall('first', 'second');
    }

    private function someLocalCall()
    {
    }
}

?>
-----
<?php

namespace Rector\PhpSpecToPHPUnit\Tests\Sets\Fixture;

use PhpSpec\ObjectBehavior;

final class KeepLocalTest extends \PHPUnit\Framework\TestCase
{
    public function testSucceed(): void
    {
        $this->someLocalCall('first', 'second');
    }

    private function someLocalCall()
    {
    }
}

?>
