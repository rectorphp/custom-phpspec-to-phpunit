<?php

declare(strict_types=1);

namespace Rector\PhpSpecToPHPUnit\Rector\Expression;

use PhpParser\Builder\Method;
use PhpParser\Node;
use PhpParser\Node\Expr\MethodCall;
use PhpParser\Node\Expr\Variable;
use PhpParser\Node\Param;
use PhpParser\Node\Scalar\String_;
use PhpParser\Node\Stmt\Expression;
use Rector\Core\Rector\AbstractRector;
use Rector\PhpSpecToPHPUnit\Enum\PhpSpecMethodName;
use Rector\PhpSpecToPHPUnit\NodeFinder\MethodCallFinder;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;

/**
 * @see \Rector\PhpSpecToPHPUnit\Tests\Rector\Expression\ShouldNotThrowRector\ShouldNotThrowRectorTest
 */
final class ShouldNotThrowRector extends AbstractRector
{
    /**
     * @return array<class-string<Node>>
     */
    public function getNodeTypes(): array
    {
        return [Expression::class];
    }

    public function getRuleDefinition(): RuleDefinition
    {
        return new RuleDefinition(
            'Handle shouldNotThrow() expectations',
            [
                new CodeSample(
                    <<<'CODE_SAMPLE'
use PhpSpec\ObjectBehavior;

class ResultSpec extends ObjectBehavior
{
    public function it_is_initializable()
    {
        $this->shouldNotThrow(Exception::class)->during(
            'someMethodCall',
            ['someArguments']
        );
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
        // should not throw an exception
        $this->someMethodCall('someArguments');
    }
}
CODE_SAMPLE
                ),
            ]
        );
    }

    /**
     * @param Expression $node
     */
    public function refactor(Node $node): ?Node
    {
        $shouldNotThrowMethodCall = MethodCallFinder::findByName($node, PhpSpecMethodName::SHOULD_NOT_THROW);
        if (! $shouldNotThrowMethodCall instanceof MethodCall) {
            return null;
        }

        // find during() method call
        $duringMethodCall = MethodCallFinder::findByName($node, PhpSpecMethodName::DURING);
        if (! $duringMethodCall instanceof MethodCall) {
            return null;
        }

        $duringArgs = $duringMethodCall->getArgs();
        if ($duringArgs === []) {
            return null;
        }

        $firstArg = $duringArgs[0];
        if (! $firstArg->value instanceof String_) {
            return null;
        }

        $string = $firstArg->value;
        $methodName = $string->value;

        $node->expr = new MethodCall(new Variable('this'), $methodName);
        return $node;
    }
}
