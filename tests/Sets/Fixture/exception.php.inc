<?php

namespace spec\Rector\PhpSpecToPHPUnit\Tests\Sets\Fixture;

use PhpSpec\ObjectBehavior;

class ExceptionSpec extends ObjectBehavior
{
    public function it_should_throw_exceptions_where_rates_are_not_loaded()
    {
        $this->beConstructedWith('https://non-existent-rates.example.com/denni_kurz.txt');

        $this->shouldThrow(RatesNotLoadedException::class)->during('get');
    }
}

?>
-----
<?php

namespace Sets\Fixture;

use Tests\Rector\PhpSpecToPHPUnit\Tests\Rector\Variable\PhpSpecToPHPUnitRector\Fixture\RatesNotLoadedException;

class ExceptionTest extends \PHPUnit\Framework\TestCase
{
    private \Sets\Fixture\Exception $exception;
    protected function setUp(): void
    {
        parent::setUp();
        $this->exception = new \Sets\Fixture\Exception();
    }
    public function testThrowExceptionsWhereRatesAreNotLoaded()
    {
        $this->exception = new \Sets\Fixture\Exception('https://non-existent-rates.example.com/denni_kurz.txt');

        $this->expectException(RatesNotLoadedException::class);
        $this->exception->get();
    }
}

?>
