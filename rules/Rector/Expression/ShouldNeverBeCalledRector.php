<?php

declare(strict_types=1);

namespace Rector\PhpSpecToPHPUnit\Rector\Expression;

use PhpParser\Node;
use PhpParser\Node\Arg;
use PhpParser\Node\Expr\MethodCall;
use PhpParser\Node\Param;
use PhpParser\Node\Stmt\Expression;
use Rector\PhpSpecToPHPUnit\Enum\PhpSpecMethodName;
use Rector\PhpSpecToPHPUnit\Enum\PHPUnitMethodName;
use Rector\PhpSpecToPHPUnit\NodeFinder\MethodCallFinder;
use Rector\Rector\AbstractRector;
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

        if (! MethodCallFinder::hasByName($node, PhpSpecMethodName::SHOULD_NOT_BE_CALLED)) {
            return null;
        }

        $topMostMethodCall = $this->resolveTopMostMethodCall($node->expr);

        $neverMethodCall = $this->nodeFactory->createLocalMethodCall(PHPUnitMethodName::NEVER);
        $args = [new Arg($neverMethodCall)];

        $topMostMethodCall->var = new MethodCall($topMostMethodCall->var, PHPUnitMethodName::EXPECTS, $args);

        return $node;
    }

    public function getRuleDefinition(): RuleDefinition
    {
        return new RuleDefinition('Change shouldNotBeCalled() to $this->expects($this->never()) check', [
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
        ]);
    }

    private function resolveTopMostMethodCall(MethodCall $methodCall): MethodCall
    {
        $nestedMethodCall = $methodCall;
        while ($nestedMethodCall->var instanceof MethodCall) {
            $nestedMethodCall = $nestedMethodCall->var;
        }

        return $nestedMethodCall;
    }
}
