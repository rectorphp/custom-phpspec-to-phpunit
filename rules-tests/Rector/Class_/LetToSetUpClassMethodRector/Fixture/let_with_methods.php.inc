<?php

namespace Rector\PhpSpecToPHPUnit\Tests\Rector\Class_\LetToSetUpClassMethodRector\Fixture;

use PhpSpec\ObjectBehavior;
use Rector\Class_\LetToSetUpClassMethodRector\Source\FirstItem;

final class LetWithMethods extends ObjectBehavior
{
    public function let(FirstItem $firstItem)
    {
        $firstItem->someMethod();
    }
}

?>
-----
<?php

namespace Rector\PhpSpecToPHPUnit\Tests\Rector\Class_\LetToSetUpClassMethodRector\Fixture;

use PhpSpec\ObjectBehavior;
use Rector\Class_\LetToSetUpClassMethodRector\Source\FirstItem;

final class LetWithMethods extends ObjectBehavior
{
    /**
     * @var \Rector\Class_\LetToSetUpClassMethodRector\Source\FirstItem|\PHPUnit\Framework\MockObject\MockObject
     */
    private \PHPUnit\Framework\MockObject\MockObject $firstItemMock;
    private \Rector\PhpSpecToPHPUnit\Tests\Rector\Class_\LetToSetUpClassMethodRector\Fixture\LetWithMethods $letWithMethods;
    protected function setUp(): void
    {
        $this->firstItemMock = $this->createMock(\Rector\Class_\LetToSetUpClassMethodRector\Source\FirstItem::class);
        $this->letWithMethods = new \Rector\PhpSpecToPHPUnit\Tests\Rector\Class_\LetToSetUpClassMethodRector\Fixture\LetWithMethods($this->firstItemMock);
        $this->firstItemMock->someMethod();
    }
}

?>
