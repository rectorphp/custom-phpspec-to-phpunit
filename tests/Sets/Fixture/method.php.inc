<?php

namespace spec\Rector\PhpSpecToPHPUnit\Tests\Sets\Fixture;

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

namespace Tests\Rector\PhpSpecToPHPUnit\Tests\Sets\Fixture;

use PhpSpec\ObjectBehavior;

class KeepMethodTest extends \PHPUnit\Framework\TestCase
{
    private \Rector\PhpSpecToPHPUnit\Tests\Sets\Fixture\KeepMethod $keepMethod;
    protected function setUp(): void
    {
        $this->keepMethod = new \Rector\PhpSpecToPHPUnit\Tests\Sets\Fixture\KeepMethod(5);

        $result = $this->getSomeNumbers();
    }

    private function getSomeNumbers()
    {
        return [1, 2, 3];
    }
}

?>
