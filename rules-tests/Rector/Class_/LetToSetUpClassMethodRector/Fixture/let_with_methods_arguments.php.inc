<?php

namespace Rector\PhpSpecToPHPUnit\Tests\Rector\Class_\LetToSetUpClassMethodRector\Fixture;

use PhpSpec\ObjectBehavior;
use Rector\Class_\LetToSetUpClassMethodRector\Source\FirstItem;
use Rector\Class_\LetToSetUpClassMethodRector\Source\SecondItem;

final class LetWithMethodsAgumentts extends ObjectBehavior
{
    public function let(FirstItem $firstItem, SecondItem $secondItem)
    {
        $firstItem->someMethod()->willReturn($secondItem);
    }
}

?>
-----
<?php

namespace Rector\PhpSpecToPHPUnit\Tests\Rector\Class_\LetToSetUpClassMethodRector\Fixture;

use PhpSpec\ObjectBehavior;
use Rector\Class_\LetToSetUpClassMethodRector\Source\FirstItem;
use Rector\Class_\LetToSetUpClassMethodRector\Source\SecondItem;

final class LetWithMethodsAgumentts extends ObjectBehavior
{
    /**
     * @var \Rector\Class_\LetToSetUpClassMethodRector\Source\FirstItem|\PHPUnit\Framework\MockObject\MockObject
     */
    private \PHPUnit\Framework\MockObject\MockObject $firstItemMock;
    /**
     * @var \Rector\Class_\LetToSetUpClassMethodRector\Source\SecondItem|\PHPUnit\Framework\MockObject\MockObject
     */
    private \PHPUnit\Framework\MockObject\MockObject $secondItemMock;
    private \Rector\PhpSpecToPHPUnit\Tests\Rector\Class_\LetToSetUpClassMethodRector\Fixture\LetWithMethodsAgumentts $letWithMethodsAgumentts;
    protected function setUp(): void
    {
        $this->firstItemMock = $this->createMock(\Rector\Class_\LetToSetUpClassMethodRector\Source\FirstItem::class);
        $this->secondItemMock = $this->createMock(\Rector\Class_\LetToSetUpClassMethodRector\Source\SecondItem::class);
        $this->letWithMethodsAgumentts = new \Rector\PhpSpecToPHPUnit\Tests\Rector\Class_\LetToSetUpClassMethodRector\Fixture\LetWithMethodsAgumentts($this->firstItemMock, $this->secondItemMock);
        $this->firstItemMock->someMethod()->willReturn($this->secondItemMock);
    }
}

?>
