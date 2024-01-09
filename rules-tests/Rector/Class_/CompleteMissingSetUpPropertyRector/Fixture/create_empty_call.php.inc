<?php

namespace Rector\PhpSpecToPHPUnit\Tests\Rector\Class_\CompleteMissingSetUpPropertyRector\Fixture;

use PHPUnit\Framework\TestCase;

final class CreateEmptyCall extends TestCase
{
    public function testSomething()
    {
        $someVariable->someCall();
    }
}

?>
-----
<?php

namespace Rector\PhpSpecToPHPUnit\Tests\Rector\Class_\CompleteMissingSetUpPropertyRector\Fixture;

use PHPUnit\Framework\TestCase;

final class CreateEmptyCall extends TestCase
{
    private $someVariable;
    protected function setUp(): void
    {
        $this->someVariable = new someVariable();
    }
    public function testSomething()
    {
        $this->someVariable->someCall();
    }
}

?>
