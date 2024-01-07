<?php

namespace Rector\PhpSpecToPHPUnit\Tests\Sets\Fixture;

use Sets\Source\Address;

final class PropertyFetchCall
{
    public function it_throws_an_exception_if_the_card_is_not_in_the_users_wallet(Address $address)
    {
        $address->getSomeMethod()->willReturn('some');

        $this->shouldThrow(\InvalidArgumentException::class)->duringHandle($address);
    }
}

?>
