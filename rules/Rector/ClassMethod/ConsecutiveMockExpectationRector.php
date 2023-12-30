<?php

declare(strict_types=1);

namespace Rector\PhpSpecToPHPUnit\Rector\ClassMethod;

use PhpParser\Node;
use PhpParser\Node\Arg;
use PhpParser\Node\Expr\MethodCall;
use PhpParser\Node\Expr\Variable;
use PhpParser\Node\Identifier;
use PhpParser\Node\Scalar\LNumber;
use PhpParser\Node\Scalar\String_;
use PhpParser\Node\Stmt\ClassMethod;
use PhpParser\Node\Stmt\Expression;
use Rector\Core\Rector\AbstractRector;
use Rector\PhpSpecToPHPUnit\Enum\PhpSpecMethodName;
use Rector\PhpSpecToPHPUnit\NodeAnalyzer\ConsecutiveMethodCallMatcher;
use Rector\PhpSpecToPHPUnit\ValueObject\MethodNameConsecutiveMethodCalls;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;

/**
 * @see \Rector\PhpSpecToPHPUnit\Tests\Rector\ClassMethod\ConsecutiveMockExpectationRector\ConsecutiveMockExpectationRectorTest
 */
final class ConsecutiveMockExpectationRector extends AbstractRector
{
    public function __construct(
        private readonly ConsecutiveMethodCallMatcher $consecutiveMethodCallMatcher,
    ) {
    }

    /**
     * @return array<class-string<Node>>
     */
    public function getNodeTypes(): array
    {
        return [ClassMethod::class];
    }

    /**
     * @param ClassMethod $node
     */
    public function refactor(Node $node): ?Node
    {
        if (! $node->isPublic() || $node->stmts === null) {
            return null;
        }

        // special case, @see https://johannespichler.com/writing-custom-phpspec-matchers/
        if ($this->isNames($node, PhpSpecMethodName::RESERVED_CLASS_METHOD_NAMES)) {
            return null;
        }

        $methodNamesConsecutiveMethodCalls = $this->consecutiveMethodCallMatcher->matchInClassMethod($node);
        if ($methodNamesConsecutiveMethodCalls === []) {
            return null;
        }

        foreach ($methodNamesConsecutiveMethodCalls as $methodNameConsecutiveMethodCalls) {
            $willReturnMapMethodCall = $this->createWillReturnMapMethodCall($methodNameConsecutiveMethodCalls);

            // replace with single->willReturnMap()
            array_splice(
                $node->stmts,
                $methodNameConsecutiveMethodCalls->getFirstStmtKey(),
                $methodNameConsecutiveMethodCalls->getMethodCallCount(),
                [new Expression($willReturnMapMethodCall)]
            );
        }

        return null;
    }

    public function getRuleDefinition(): RuleDefinition
    {
        return new RuleDefinition('Merge consecutive mock expectations to single ->willReturnMap() call', [
            new CodeSample(
                <<<'CODE_SAMPLE'
use PhpSpec\ObjectBehavior;

class DuringMethodSpec extends ObjectBehavior
{
    public function is_should(MockedType $mockedType)
    {
        $mockedType->set('first_key', 100)->shouldBeCalled();
        $mockedType->set('second_key', 200)->shouldBeCalled();
    }
}
CODE_SAMPLE
                ,
                <<<'CODE_SAMPLE'
use PhpSpec\ObjectBehavior;

class DuringMethodSpec extends ObjectBehavior
{
    public function is_should(MockedType $mockedType)
    {
        $mockedType->expects($this->exactly(2))->method('set')
            ->willReturnMap([
                ['first_key', 100],
                ['second_key', 200],
            ]);
    }
}
CODE_SAMPLE
            ),
        ]);
    }

    private function createWillReturnMapMethodCall(
        MethodNameConsecutiveMethodCalls $methodNameConsecutiveMethodCalls
    ): MethodCall {
        $exactlyMethodCall = new MethodCall(new Variable('this'), new Identifier('exactly'), [
            new Arg(new LNumber($methodNameConsecutiveMethodCalls->getMethodCallCount())),
        ]);

        $expectsMethodCall = new MethodCall($methodNameConsecutiveMethodCalls->getMockVariable(), new Identifier(
            'expects'
        ), [new Arg($exactlyMethodCall)]);

        $methodMethodCall = new MethodCall($expectsMethodCall, new Identifier('method'), [
            new Arg(new String_($methodNameConsecutiveMethodCalls->getMethodName())),
        ]);

        return $methodMethodCall;
    }
}
