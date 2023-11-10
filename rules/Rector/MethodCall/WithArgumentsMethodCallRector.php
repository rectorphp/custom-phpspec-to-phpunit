<?php

declare(strict_types=1);

namespace Rector\PhpSpecToPHPUnit\Rector\MethodCall;

use PhpParser\Node;
use PhpParser\Node\Expr\MethodCall;
use PhpParser\Node\Expr\StaticCall;
use PhpParser\Node\Expr\Variable;
use PhpParser\Node\Param;
use Rector\Core\Rector\AbstractRector;
use Rector\PhpSpecToPHPUnit\Enum\PhpSpecMethodName;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;

/**
 * @see \Rector\PhpSpecToPHPUnit\Tests\Rector\MethodCall\WithArgumentsMethodCallRector\WithArgumentsMethodCallRectorTest
 */
final class WithArgumentsMethodCallRector extends AbstractRector
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
        if (! $this->isName($node->name, PhpSpecMethodName::WITH)) {
            return null;
        }

        $args = $node->getArgs();
        if (count($args) !== 1) {
            return null;
        }

        $firstArg = $args[0];
        if (! $firstArg->value instanceof StaticCall) {
            return null;
        }

        $staticCall = $firstArg->value;
        if (! $this->isName($staticCall->class, 'Prophecy\Argument')) {
            return null;
        }

        if (! $this->isName($staticCall->name, 'cetera')) {
            return null;
        }

        $thisAnyMethodCall = new MethodCall(new Variable('this'), 'any');
        $firstArg->value = $thisAnyMethodCall;

        return $node;
    }

    public function getRuleDefinition(): RuleDefinition
    {
        return new RuleDefinition(
            'Migrate ->with(Arguments::*()) call to PHPUnit',
            [
                new CodeSample(
                    <<<'CODE_SAMPLE'
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;

class ResultSpec extends ObjectBehavior
{
    public function it_is_initializable()
    {
        $this->run()->with(Arguments::cetera());
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
        $this->run()->with($this->any());
    }
}
CODE_SAMPLE
                ),

            ]
        );
    }
}
