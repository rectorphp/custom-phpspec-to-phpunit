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

        $secondTypeOne->getName()->shouldBeCalled();
        $secondTypeOne->validate(123)->shouldBeCalled();

        $secondTypeTwo->getName()->shouldBeCalled();
        $secondTypeTwo->validate(456)->shouldBeCalled();

        $this->someMethodCall(new BinToValidate());
    }
}

?>
-----
<?php

namespace Tests\Fixture\Project;

use PhpSpec\ObjectBehavior;

final class BinValidatorTest extends \PHPUnit\Framework\TestCase
{
    public function testInvoke(): void
    {
        /** @var \spec\Fixture\Project\SecondType|\PHPUnit\Framework\MockObject\MockObject $secondTypeOneMock */
        $secondTypeOneMock = $this->createMock(\spec\Fixture\Project\SecondType::class);
        /** @var \spec\Fixture\Project\SecondType|\PHPUnit\Framework\MockObject\MockObject $secondTypeTwoMock */
        $secondTypeTwoMock = $this->createMock(\spec\Fixture\Project\SecondType::class);
        $this->binValidator = new \Fixture\Project\BinValidator($secondTypeOneMock, $secondTypeTwoMock);
        $secondTypeOneMock->expects($this->once())->method('getName');
        $secondTypeOneMock->expects($this->once())->method('validate')->with(123);
        $secondTypeTwoMock->expects($this->once())->method('getName');
        $secondTypeTwoMock->expects($this->once())->method('validate')->with(456);
        $this->binValidator->someMethodCall(new BinToValidate());
    }
}

?>
