<?php

namespace Sets\Fixture;

use PhpSpec\ObjectBehavior;
use spec\Rector\PhpSpecToPHPUnit\Tests\Rector\Variable\PhpSpecToPHPUnitRector\Fixture\CurrencyData;
use spec\Rector\PhpSpecToPHPUnit\Tests\Rector\Variable\PhpSpecToPHPUnitRector\Fixture\ValidationException;

class CurrencySpec extends ObjectBehavior
{
    public function let(CurrencyData $data)
    {
        $data->code = 'CZK';

        $this->beConstructedThrough('create', [$data]);
    }

    public function it_should_not_be_constructed_without_code(CurrencyData $data)
    {
        $data->code = '';

        $this->beConstructedThrough('create', [$data]);
        $this->shouldThrow(ValidationException::class)->duringInstantiation();
    }
}

?>
-----
<?php

namespace Tests\Rector\PhpSpecToPHPUnit\Tests\Rector\Variable\PhpSpecToPHPUnitRector\Fixture;

class CurrencyTest extends \PHPUnit\Framework\TestCase
{
    private \Sets\Fixture\Currency $currency;
    private \PHPUnit\Framework\MockObject\MockObject|\spec\Rector\PhpSpecToPHPUnit\Tests\Rector\Variable\PhpSpecToPHPUnitRector\Fixture\CurrencyData $data;
    protected function setUp(): void
    {
        $this->data = $this->createMock(CurrencyData::class);
        $this->data->code = 'CZK';
        $this->currency = \Sets\Fixture\Currency::create($this->data);
    }

    public function testNotBeConstructedWithoutCode()
    {
        $this->data->code = '';
        $this->expectException(ValidationException::class);
        $this->currency = \Sets\Fixture\Currency::create($this->data);
    }
}

?>
