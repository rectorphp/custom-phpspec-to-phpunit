<?php

namespace spec\Rector\PhpSpecToPHPUnit\Fixture;

use PhpSpec\ObjectBehavior;

class ExceptionSpec extends ObjectBehavior
{
    public function it_should_throw_exceptions()
    {
        $this->beConstructedWith('random-string');

        $this->shouldThrow(RatesNotLoadedException::class)->during('get');
    }
}

?>
-----
<?php

namespace Tests\Rector\PhpSpecToPHPUnit\Fixture;

use PhpSpec\ObjectBehavior;

class ExceptionTest extends \PHPUnit\Framework\TestCase
{
    public function testThrowExceptions(): void
    {
        $exception = new \Rector\PhpSpecToPHPUnit\Fixture\Exception('random-string');
        $this->expectException(RatesNotLoadedException::class);
        $exception->get();
    }
}

?>
