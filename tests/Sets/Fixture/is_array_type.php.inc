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
    public function testArrayType(): void
    {
        $this->assertIsIterable($isArrayType->shippingAddresses());
    }
}

?>
