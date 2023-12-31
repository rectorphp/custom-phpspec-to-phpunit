<?php

declare(strict_types=1);

namespace Rector\PhpSpecToPHPUnit\Rector\ClassMethod;

use PhpParser\Node;
use PhpParser\Node\Stmt\ClassMethod;
use PhpParser\Node\Stmt\Expression;
use Rector\Core\Rector\AbstractRector;
use Rector\PhpSpecToPHPUnit\Enum\PhpSpecMethodName;
use Rector\PhpSpecToPHPUnit\NodeAnalyzer\ConsecutiveMethodCallMatcher;
use Rector\PhpSpecToPHPUnit\NodeFactory\WillReturnMapMethodCallFactory;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;

/**
 * @see \Rector\PhpSpecToPHPUnit\Tests\Rector\ClassMethod\ConsecutiveMockExpectationRector\ConsecutiveMockExpectationRectorTest
 */
final class ConsecutiveMockExpectationRector extends AbstractRector
{
    /**
     * @readonly
     * @var \Rector\PhpSpecToPHPUnit\NodeAnalyzer\ConsecutiveMethodCallMatcher
     */
    private $consecutiveMethodCallMatcher;
    /**
     * @readonly
     * @var \Rector\PhpSpecToPHPUnit\NodeFactory\WillReturnMapMethodCallFactory
     */
    private $willReturnMapMethodCallFactory;
    public function __construct(ConsecutiveMethodCallMatcher $consecutiveMethodCallMatcher, WillReturnMapMethodCallFactory $willReturnMapMethodCallFactory)
    {
        $this->consecutiveMethodCallMatcher = $consecutiveMethodCallMatcher;
        $this->willReturnMapMethodCallFactory = $willReturnMapMethodCallFactory;
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

        foreach ($methodNamesConsecutiveMethodCalls as $methodNameConsecutiveMethodCall) {
            $willReturnMapMethodCall = $this->willReturnMapMethodCallFactory->create($methodNameConsecutiveMethodCall);

            // replace with single->willReturnMap()
            array_splice(
                $node->stmts,
                $methodNameConsecutiveMethodCall->getFirstStmtKey(),
                $methodNameConsecutiveMethodCall->getMethodCallCount(),
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
        $mockedType->set('first_key')->shouldReturn(100);
        $mockedType->set('second_key')->shouldReturn(200);
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
}
