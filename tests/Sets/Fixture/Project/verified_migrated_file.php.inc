<?php

namespace spec\Fixture\Project;

use PhpSpec\ObjectBehavior;

class BinValidatorSpec extends ObjectBehavior
{
    public function it_should_invoke(
        SecondType $secondTypeOne,
        SecondType $secondTypeTwo
    ) {
        $this->beConstructedWith($secondTypeOne, $secondTypeTwo);
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
        /** @var \spec\Fixture\Project\SecondType|\PHPUnit\Framework\MockObject\MockObject $secondTypeOneMock */
        $secondTypeOneMock = $this->createMock(\spec\Fixture\Project\SecondType::class);
        /** @var \spec\Fixture\Project\SecondType|\PHPUnit\Framework\MockObject\MockObject $secondTypeTwoMock */
        $secondTypeTwoMock = $this->createMock(\spec\Fixture\Project\SecondType::class);
        $binValidator = new \Fixture\Project\BinValidator($secondTypeOneMock, $secondTypeTwoMock);
        $bin = '123456';
        $secondTypeOneMock->expects($this->once())->method('getName');
        $secondTypeOneMock->expects($this->once())->method('validate')->with($this->equalTo($bin));

        $secondTypeTwoMock->expects($this->once())->method('getName');
        $secondTypeTwoMock->expects($this->once())->method('validate')->with($this->equalTo($bin));
        $binValidator->someMethodCall(new BinToValidate($bin));
    }
}

?>
