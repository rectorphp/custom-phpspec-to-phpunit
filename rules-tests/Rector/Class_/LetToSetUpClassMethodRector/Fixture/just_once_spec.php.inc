<?php

namespace Rector\PhpSpecToPHPUnit\Tests\Rector\Class_\LetToSetUpClassMethodRector\Fixture;

use PhpSpec\ObjectBehavior;
use Rector\Class_\LetToSetUpClassMethodRector\Source\FirstItem;
use Rector\Class_\LetToSetUpClassMethodRector\Source\SecondItem;

final class JustOnce extends ObjectBehavior
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
     * @var MockObject<FirstItem>
     */
    private MockObject $firstItemMock;
    /**
     * @var MockObject<SecondItem>
     */
    private MockObject $secondItemMock;
    private \Rector\PhpSpecToPHPUnit\Tests\Rector\Class_\LetToSetUpClassMethodRector\Fixture\JustOnce $justOnce;
    protected function setUp(): void
    {
        $this->firstItemMock = $this->createMock(FirstItem::class);
        $this->secondItemMock = $this->createMock(SecondItem::class);
        $this->justOnce = new JustOnce($this->firstItemMock, $this->secondItemMock);
    }
}

?>
