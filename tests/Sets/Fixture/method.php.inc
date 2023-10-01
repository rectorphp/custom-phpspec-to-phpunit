<?php

namespace spec\Rector\PhpSpecToPHPUnit\Tests\Rector\Variable\PhpSpecToPHPUnitRector\Fixture;

use PhpSpec\ObjectBehavior;

class KeepMethodSpec extends ObjectBehavior
{
    public function let()
    {
        $this->beConstructedWith(5);

        $result = $this->getSomeNumbers();
    }

    private function getSomeNumbers()
    {
        return [1, 2, 3];
    }
}

?>
-----
<?php

namespace Sets\Fixture;

class KeepMethodTest extends \PHPUnit\Framework\TestCase
{
    private \Sets\Fixture\KeepMethod $keepMethod;
    protected function setUp(): void
    {
        $this->keepMethod = new \Sets\Fixture\KeepMethod(5);

        $result = $this->getSomeNumbers();
    }

    private function getSomeNumbers()
    {
        return [1, 2, 3];
    }
}

?>