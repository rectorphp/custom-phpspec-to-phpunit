<?php

namespace Rector\PhpSpecToPHPUnit\Tests\Rector\Expression\ShouldNotThrowRector\Fixture;

use PhpSpec\ObjectBehavior;

class ShouldNotThrow extends ObjectBehavior
{
    public function it_does_test()
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

namespace Rector\PhpSpecToPHPUnit\Tests\Rector\Expression\ShouldNotThrowRector\Fixture;

use PhpSpec\ObjectBehavior;

class ShouldNotThrow extends ObjectBehavior
{
    public function it_does_test()
    {
        /** should not throw exception */
        $this->shouldNotThrow->someMethodCall('someArguments');
    }
}

?>
