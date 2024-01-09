<?php

namespace Rector\PhpSpecToPHPUnit\Tests\Rector\Class_\SplitSetUpMockRector\Fixture;

use PHPUnit\Framework\TestCase;

final class SomeSetUpMockCalls extends TestCase
{
    private $someClassMock;

    protected function setUp(): void
    {
        $this->someClassMock = $this->createMock(SomeClass::class);
        $this->someClassMock->expect($this->once())->method('someMethod')->willReturn('someValue');
    }

    public function testSome()
    {
        $item = 1;
    }

    public function testAnother()
    {
        $item = 2;
        $this->someClassMock->expect($this->once())->method('someMethod')->willReturn('differentValue');
    }
}

?>
-----
<?php

namespace Rector\PhpSpecToPHPUnit\Tests\Rector\Class_\SplitSetUpMockRector\Fixture;

use PHPUnit\Framework\TestCase;

final class SomeSetUpMockCalls extends TestCase
{
    private $someClassMock;

    protected function setUp(): void
    {
        $this->someClassMock = $this->createMock(SomeClass::class);
    }

    public function testSome()
    {
        $this->someClassMock->expect($this->once())->method('someMethod')->willReturn('someValue');
        $item = 1;
    }

    public function testAnother()
    {
        $item = 2;
        $this->someClassMock->expect($this->once())->method('someMethod')->willReturn('differentValue');
    }
}

?>
