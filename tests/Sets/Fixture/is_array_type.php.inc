<?php

namespace Rector\PhpSpecToPHPUnit\Tests\Sets\Fixture;

use PhpSpec\ObjectBehavior;
use Sets\Source\Address;
use Sets\Source\Cart;

class IsArrayType extends ObjectBehavior
{
    public function is_array_type()
    {
        $this->shippingAddresses()->shouldBeArray();
    }
}

?>
-----
<?php

namespace Rector\PhpSpecToPHPUnit\Tests\Sets\Fixture;

use PhpSpec\ObjectBehavior;
use Sets\Source\Address;
use Sets\Source\Cart;

final class IsArrayTypeTest extends \PHPUnit\Framework\TestCase
{
    private \Rector\PhpSpecToPHPUnit\Tests\Sets\Fixture\IsArrayType $isArrayType;
    protected function setUp(): void
    {
        $this->isArrayType = new \Rector\PhpSpecToPHPUnit\Tests\Sets\Fixture\IsArrayType();
    }
    public function testArrayType(): void
    {
        $this->assertIsIterable($this->isArrayType->shippingAddresses());
    }
}

?>
