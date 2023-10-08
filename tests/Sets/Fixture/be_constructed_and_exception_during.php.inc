<?php

namespace spec\Rector\PhpSpecToPHPUnit\Fixture;

use PhpSpec\ObjectBehavior;

class ExceptionSpec extends ObjectBehavior
{
    public function it_should_throw_exceptions_where_rates_are_not_loaded()
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
    protected function setUp(): void
    {
        parent::setUp();
        $this->exception = new \Rector\PhpSpecToPHPUnit\Fixture\Exception();
    }
    public function testThrowExceptionsWhereRatesAreNotLoaded(): void
    {
        $this->exception = new \Rector\PhpSpecToPHPUnit\Fixture\Exception('random-string');
        $this->expectException(RatesNotLoadedException::class);
        $this->exception->get();
    }
}

?>
