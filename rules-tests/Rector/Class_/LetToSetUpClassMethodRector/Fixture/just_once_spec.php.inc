<?php

namespace Rector\PhpSpecToPHPUnit\Tests\Rector\Class_\LetToSetUpClassMethodRector\Fixture;

use PhpSpec\ObjectBehavior;
use Rector\Class_\LetToSetUpClassMethodRector\Source\FirstItem;
use Rector\Class_\LetToSetUpClassMethodRector\Source\SecondItem;

final class JustOnceSpec extends ObjectBehavior
{
    public function let(FirstItem $firstItem, SecondItem $secondItem)
    {
    }
}

?>
-----
<?php

namespace Rector\PhpSpecToPHPUnit\Tests\Rector\Class_\LetToSetUpClassMethodRector\Fixture;

use PhpSpec\ObjectBehavior;
use Rector\Class_\LetToSetUpClassMethodRector\Source\FirstItem;
use Rector\Class_\LetToSetUpClassMethodRector\Source\SecondItem;

final class JustOnceSpec extends ObjectBehavior
{
    /**
     * @var \Rector\Class_\LetToSetUpClassMethodRector\Source\FirstItem|\PHPUnit\Framework\MockObject\MockObject
     */
    private \PHPUnit\Framework\MockObject\Generator\MockType $firstItemMock;
    /**
     * @var \Rector\Class_\LetToSetUpClassMethodRector\Source\SecondItem|\PHPUnit\Framework\MockObject\MockObject
     */
    private \PHPUnit\Framework\MockObject\Generator\MockType $secondItemMock;
    private \Rector\PhpSpecToPHPUnit\Tests\Rector\Class_\LetToSetUpClassMethodRector\Fixture\JustOnce $justOnce;
    protected function setUp(): void
    {
        $this->firstItemMock = $this->createMock(\Rector\Class_\LetToSetUpClassMethodRector\Source\FirstItem::class);
        $this->secondItemMock = $this->createMock(\Rector\Class_\LetToSetUpClassMethodRector\Source\SecondItem::class);
        $this->justOnce = new \Rector\PhpSpecToPHPUnit\Tests\Rector\Class_\LetToSetUpClassMethodRector\Fixture\JustOnce($this->firstItemMock, $this->secondItemMock);
    }
}

?>
