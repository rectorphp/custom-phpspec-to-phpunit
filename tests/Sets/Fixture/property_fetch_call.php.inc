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
-----
<?php

namespace Rector\PhpSpecToPHPUnit\Tests\Sets\Fixture;

use Sets\Source\Address;

final class PropertyFetchCallTest extends \PHPUnit\Framework\TestCase
{
    private \Rector\PhpSpecToPHPUnit\Tests\Sets\Fixture\PropertyFetchCall $propertyFetchCall;
    protected function setUp(): void
    {
        $this->propertyFetchCall = new \Rector\PhpSpecToPHPUnit\Tests\Sets\Fixture\PropertyFetchCall();
    }
    public function testThrowsAnExceptionIfTheCardIsNotInTheUsersWallet(): void
    {
        /** @var \Sets\Source\Address|\PHPUnit\Framework\MockObject\MockObject $addressMock */
        $addressMock = $this->createMock(\Sets\Source\Address::class);
        $addressMock->expects($this->once())->method('getSomeMethod')->willReturn('some');
        $this->expectException(\InvalidArgumentException::class);
        $this->propertyFetchCall->handle($addressMock);
    }
}

?>
