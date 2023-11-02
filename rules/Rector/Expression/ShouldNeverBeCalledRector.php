<?php

declare(strict_types=1);

namespace Rector\PhpSpecToPHPUnit\Rector\Expression;

use PhpParser\Builder\Method;
use PhpParser\Node;
use PhpParser\Node\Arg;
use PhpParser\Node\Expr\MethodCall;
use PhpParser\Node\Param;
use PhpParser\Node\Stmt\Expression;
use Rector\Core\Rector\AbstractRector;
use Rector\PhpSpecToPHPUnit\Enum\PhpSpecMethodName;
use Rector\PhpSpecToPHPUnit\Enum\PHPUnitMethodName;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;

/**
 * @see \Rector\PhpSpecToPHPUnit\Tests\Rector\Expression\ShouldNeverBeCalledRector\ShouldNeverBeCalledRectorTest
 */
final class ShouldNeverBeCalledRector extends AbstractRector
{
    /**
     * @return array<class-string<Node>>
     */
    public function getNodeTypes(): array
    {
        return [Expression::class];
    }

    /**
     * @param Expression $node
     */
    public function refactor(Node $node): ?Node
    {
        if (! $node->expr instanceof MethodCall) {
            return null;
        }

        $hasFound = false;

        // find shouldNotBeCalled() as part of method call chain
        $this->traverseNodesWithCallable($node, function (Node $node) use (&$hasFound) {
            if (! $node instanceof MethodCall) {
                return null;
            }

            if (! $this->isName($node->name, PhpSpecMethodName::SHOULD_NOT_BE_CALLED)) {
                return null;
            }

            $hasFound = true;

            // remove call
            return $node->var;
        });

        if ($hasFound === false) {
            return null;
        }

        /** @var MethodCall $nestedMethodCall */
        $nestedMethodCall = $node->expr;

        $thisOnceMethodCall = $this->nodeFactory->createLocalMethodCall(PHPUnitMethodName::NEVER);
        $args = [new Arg($thisOnceMethodCall)];

        while ($nestedMethodCall->var instanceof MethodCall) {
            $nestedMethodCall = $nestedMethodCall->var;
        }

        // topmost method call
        $nestedMethodCall->var = new MethodCall($nestedMethodCall->var, PHPUnitMethodName::EXPECTS, $args);
        return $node;
    }

    public function getRuleDefinition(): RuleDefinition
    {
        return new RuleDefinition(
            'Handle shouldNotBeCalled() expectations',
            [
                new CodeSample(
                    <<<'CODE_SAMPLE'
use PhpSpec\ObjectBehavior;

class ResultSpec extends ObjectBehavior
{
    public function it_is_initializable()
    {
        $this->run()->shouldNotBeCalled();
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
        $this->expects($this->never())->run();
    }
}
CODE_SAMPLE
                ),
            ]
        );
    }
}
