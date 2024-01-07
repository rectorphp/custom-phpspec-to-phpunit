<?php

declare(strict_types=1);

namespace Rector\PhpSpecToPHPUnit\Rector\MethodCall;

use PhpParser\Node;
use PhpParser\Node\Expr\MethodCall;
use PhpParser\Node\Param;
use Rector\PhpSpecToPHPUnit\Enum\PhpSpecMethodName;
use Rector\Rector\AbstractRector;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;

/**
 * @see \Rector\PhpSpecToPHPUnit\Tests\Rector\MethodCall\RemoveShouldBeCalledRector\RemoveShouldBeCalledRectorTest
 */
final class RemoveShouldBeCalledRector extends AbstractRector
{
    /**
     * @return array<class-string<Node>>
     */
    public function getNodeTypes(): array
    {
        return [MethodCall::class];
    }

    /**
     * @param MethodCall $node
     */
    public function refactor(Node $node): ?Node
    {
        if ($this->isName($node->name, PhpSpecMethodName::SHOULD_BE_CALLED)) {
            // The shouldBeCalled() is implicit and not needed, handled by another rule
            return $node->var;
        }

        if ($this->isName($node->name, PhpSpecMethodName::SHOULD_NOT_BE_CALLED)) {
            // The shouldNeverBeCalled() is implicit and not needed, handled by another rule
            return $node->var;
        }

        if ($this->isName($node->name, PhpSpecMethodName::WILL_RETURN) && $node->getArgs() === []) {
            return $node->var;
        }

        return null;
    }

    public function getRuleDefinition(): RuleDefinition
    {
        return new RuleDefinition(
            'Remove shouldBeCalled() as implicit in PHPUnit, also empty willReturn() as no return is implicit in PHPUnit',
            [
                new CodeSample(
                    <<<'CODE_SAMPLE'
use PhpSpec\ObjectBehavior;

class ResultSpec extends ObjectBehavior
{
    public function it_is_initializable()
    {
        $this->run()->shouldBeCalled();

        $this->go()->willReturn();
    }
}
CODE_SAMPLE
                    ,
                    <<<'CODE_SAMPLE'
use PhpSpec\ObjectBehavior;

class ResultSpec extends ObjectBehavior
{
    public function it_is_initializable()
    {
        $this->run();

        $this->go();
    }
}
CODE_SAMPLE
                ),

            ]
        );
    }
}
