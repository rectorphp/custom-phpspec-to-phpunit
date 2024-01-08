<?php

namespace Sets\Fixture;

use Ecommerce\Cart\Cart;
use Ecommerce\Cart\Exception\NotFoundException;
use Ecommerce\Component\Factory\EntityFactory;
use Ecommerce\Pricing\Currency\DefaultCurrency;
use Ecommerce\Pricing\Currency\DummyDefaultCurrencyProvider;
use Ecommerce\Pricing\Price\CalculatedPrice;
use Ecommerce\Pricing\Price\Price;
use Ecommerce\Taxing\PriceType;
use PhpSpec\ObjectBehavior;
use Ramsey\Uuid\Uuid;

class CartSpec extends ObjectBehavior
{
    public function let()
    {
        DefaultCurrency::setProvider(new DummyDefaultCurrencyProvider());

        $this->beConstructedWith(Uuid::uuid1());
    }

    public function it_should_add_new_item()
    {
        $id       = Uuid::uuid1();
        $taxRate  = EntityFactory::make('tax_rate_21');
        $currency = EntityFactory::make('currency_czk');

        $this->add(new \Ecommerce\Cart\Item($id, 'Lorem ipsum', 4, new Price(671, $taxRate, $currency)));

        $item = $this->item($id);

        $item->id()->shouldReturn($id);
        $item->id()->shouldReturn(null);
        $item->quantity()->shouldReturn(4);
    }

    public function it_should_provide_item_by_its_identifier()
    {
        $id       = Uuid::uuid1();
        $taxRate  = EntityFactory::make('tax_rate_21');
        $currency = EntityFactory::make('currency_czk');

        $this->add(new \Ecommerce\Cart\Item($id, 'Lorem ipsum', 3, new Price(671, $taxRate, $currency)));

        $this->item($id)->shouldBeAnInstanceOf(\Ecommerce\Cart\Contract\Item::class);

        $item = $this->item($id);
        $item->getContent()->shouldContain('word');
        $item->quantity()->shouldReturn(10);
    }

    public function it_should_calculate_total_price_of_all_items_when_not_empty()
    {
        PriceType::set(PriceType::WITH_VAT);

        $taxRate  = EntityFactory::make('tax_rate_21');
        $currency = EntityFactory::make('currency_czk');

        $this->add(new \Ecommerce\Cart\Item(Uuid::uuid1(), 'Lorem ipsum', 3, new Price(671, $taxRate, $currency)));

        $price = $this->price();

        $price->shouldReturnAnInstanceOf(CalculatedPrice::class);

        $price->withVat()->shouldReturn(2013.00);
        $price->withoutVat()->shouldReturn(1663.54);
        $price->vat()->shouldReturn(349.46);
    }
}

?>
-----
<?php

namespace Sets\Fixture;

use Ecommerce\Cart\Cart;
use Ecommerce\Cart\Exception\NotFoundException;
use Ecommerce\Component\Factory\EntityFactory;
use Ecommerce\Pricing\Currency\DefaultCurrency;
use Ecommerce\Pricing\Currency\DummyDefaultCurrencyProvider;
use Ecommerce\Pricing\Price\CalculatedPrice;
use Ecommerce\Pricing\Price\Price;
use Ecommerce\Taxing\PriceType;
use PhpSpec\ObjectBehavior;
use Ramsey\Uuid\Uuid;

final class CartTest extends \PHPUnit\Framework\TestCase
{
    private \Sets\Fixture\Cart $cart;
    protected function setUp(): void
    {
        DefaultCurrency::setProvider(new DummyDefaultCurrencyProvider());

        $this->cart = new \Sets\Fixture\Cart(Uuid::uuid1());
    }

    public function testAddNewItem(): void
    {
        $id       = Uuid::uuid1();
        $taxRate  = EntityFactory::make('tax_rate_21');
        $currency = EntityFactory::make('currency_czk');

        $this->cart->add(new \Ecommerce\Cart\Item($id, 'Lorem ipsum', 4, new Price(671, $taxRate, $currency)));

        $item = $this->cart->item($id);

        $this->assertSame($id, $item->id());
        $this->assertNull($item->id());
        $this->assertSame(4, $item->quantity());
    }

    public function testProvideItemByItsIdentifier(): void
    {
        $id       = Uuid::uuid1();
        $taxRate  = EntityFactory::make('tax_rate_21');
        $currency = EntityFactory::make('currency_czk');

        $this->cart->add(new \Ecommerce\Cart\Item($id, 'Lorem ipsum', 3, new Price(671, $taxRate, $currency)));

        $this->assertInstanceOf(\Ecommerce\Cart\Contract\Item::class, $this->cart->item($id));

        $item = $this->cart->item($id);
        $this->assertStringContainsString('word', $item->getContent());
        $this->assertSame(10, $item->quantity());
    }

    public function testCalculateTotalPriceOfAllItemsWhenNotEmpty(): void
    {
        PriceType::set(PriceType::WITH_VAT);

        $taxRate  = EntityFactory::make('tax_rate_21');
        $currency = EntityFactory::make('currency_czk');

        $this->cart->add(new \Ecommerce\Cart\Item(Uuid::uuid1(), 'Lorem ipsum', 3, new Price(671, $taxRate, $currency)));

        $price = $this->cart->price();

        $this->assertInstanceOf(CalculatedPrice::class, $price);

        $this->assertSame(2013.00, $price->withVat());
        $this->assertSame(1663.54, $price->withoutVat());
        $this->assertSame(349.46, $price->vat());
    }
}

?>
