<?php

namespace Rector\PhpSpecToPHPUnit\Tests\Rector\Class_\LearnFromPHPUnitReportRector\Fixture;

use PHPUnit\Framework\TestCase;

final class MoreThanOnce extends TestCase
{
    public function testSomething()
    {
        $this->someMock->expect($this->once())->method('someMethod');
    }
}

?>
-----
<?php

namespace Rector\PhpSpecToPHPUnit\Tests\Rector\Class_\LearnFromPHPUnitReportRector\Fixture;

use PHPUnit\Framework\TestCase;

final class MoreThanOnce extends TestCase
{
    public function testSomething()
    {
        $this->someMock->expect($this->atLeastOnce())->method('someMethod');
    }
}

?>
