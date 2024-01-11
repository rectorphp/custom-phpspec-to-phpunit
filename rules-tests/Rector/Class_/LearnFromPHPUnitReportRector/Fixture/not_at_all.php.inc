<?php

namespace Rector\PhpSpecToPHPUnit\Tests\Rector\Class_\LearnFromPHPUnitReportRector\Fixture;

use PHPUnit\Framework\TestCase;

final class NotAtAll extends TestCase
{
    public function testAnything()
    {
        $this->someMock->expect($this->once())->method('callMe');
    }
}

?>
-----
<?php

namespace Rector\PhpSpecToPHPUnit\Tests\Rector\Class_\LearnFromPHPUnitReportRector\Fixture;

use PHPUnit\Framework\TestCase;

final class NotAtAll extends TestCase
{
    public function testAnything()
    {
    }
}

?>
