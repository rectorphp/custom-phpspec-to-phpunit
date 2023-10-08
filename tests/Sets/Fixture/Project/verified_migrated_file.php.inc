<?php

namespace spec\Fixture\Project;

use PhpSpec\ObjectBehavior;

class BinValidatorSpec extends ObjectBehavior
{
    public function it_should_invoke(
        FirstType $firstType,
        SecondType $secondTypeOne,
        SecondType $secondTypeTwo
    ) {
        $this->beConstructedWith($firstType, $secondTypeOne, $secondTypeTwo);
        $bin = '123456';

        $secondTypeOne->getName()->shouldBeCalled();
        $secondTypeOne->validate($bin)->shouldBeCalled();

        $secondTypeTwo->getName()->shouldBeCalled();
        $secondTypeTwo->validate($bin)->shouldBeCalled();

        $this->someMethodCall(new BinToValidate($bin));
    }
}

?>
-----
<?php

namespace Tests\Fixture\Project;

use PhpSpec\ObjectBehavior;

class BinValidatorTest extends \PHPUnit\Framework\TestCase
{
    public function testInvoke(): void
    {
        /** @var FirstType|\PHPUnit\Framework\MockObject\MockObject $firstType */
        $firstType = $this->createMock(FirstType::class);
        /** @var SecondType|\PHPUnit\Framework\MockObject\MockObject $secondTypeOne */
        $secondTypeOne = $this->createMock(SecondType::class);
        /** @var SecondType|\PHPUnit\Framework\MockObject\MockObject $secondTypeTwo */
        $secondTypeTwo = $this->createMock(SecondType::class);
        $binValidator = new \Fixture\Project\BinValidator($firstType, $secondTypeOne, $secondTypeTwo);
        $bin = '123456';
        $secondTypeOne->expects($this->atLeastOnce())->method('getName');
        $secondTypeOne->expects($this->atLeastOnce())->method('validate')->with($this->equalTo($bin));
        $secondTypeTwo->expects($this->atLeastOnce())->method('getName');
        $secondTypeTwo->expects($this->atLeastOnce())->method('validate')->with($this->equalTo($bin));
        $binValidator->someMethodCall(new BinToValidate($bin));
    }
}

?>
