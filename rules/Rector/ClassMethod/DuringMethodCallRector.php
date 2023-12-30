<?php

declare(strict_types=1);

namespace Rector\PhpSpecToPHPUnit\Rector\ClassMethod;

use PhpParser\Node;
use PhpParser\Node\Stmt\ClassMethod;
use Rector\Core\Rector\AbstractRector;
use Rector\PhpSpecToPHPUnit\Enum\PhpSpecMethodName;
use Rector\PhpSpecToPHPUnit\NodeAnalyzer\DuringAndRelatedMethodCallMatcher;
use Rector\PhpSpecToPHPUnit\NodeFactory\ExpectExceptionMethodCallFactory;
use Rector\PhpSpecToPHPUnit\ValueObject\DuringAndRelatedMethodCall;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;

/**
 * @see \Rector\PhpSpecToPHPUnit\Tests\Rector\ClassMethod\ShouldThrowAndInstantiationOrderRector\ShouldThrowAndInstantiationOrderRectorTest
 */
final class DuringMethodCallRector extends AbstractRector
{
    public function __construct(
        private readonly DuringAndRelatedMethodCallMatcher $duringAndRelatedMethodCallMatcher,
        private readonly ExpectExceptionMethodCallFactory $expectExceptionMethodCallFactory,
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

        foreach ($node->stmts as $key => $stmt) {
            $duringAndRelatedMethodCall = $this->duringAndRelatedMethodCallMatcher->match(
                $stmt,
                PhpSpecMethodName::DURING
            );

            if (! $duringAndRelatedMethodCall instanceof DuringAndRelatedMethodCall) {
                continue;
            }

            $newStmts = $this->expectExceptionMethodCallFactory->createExpectExceptionStmts(
                $duringAndRelatedMethodCall
            );
            $newStmts[] = $this->expectExceptionMethodCallFactory->createMethodCallStmt($duringAndRelatedMethodCall);

            array_splice($node->stmts, $key, 1, $newStmts);

            return $node;
        }

        return null;
    }

    public function getRuleDefinition(): RuleDefinition
    {
        return new RuleDefinition('Split shouldThrow() and during() method to expected exception and method call', [
            new CodeSample(
                <<<'CODE_SAMPLE'
use PhpSpec\ObjectBehavior;

class DuringMethodSpec extends ObjectBehavior
{
    public function is_should()
    {
        $this->shouldThrow(ValidationException::class)->during('someMethod');
    }
}
CODE_SAMPLE
                ,
                <<<'CODE_SAMPLE'
use PhpSpec\ObjectBehavior;

class DuringMethodSpec extends ObjectBehavior
{
    public function is_should()
    {
        $this->expectException(ValidationException::class);
        $this->someMethod();
    }
}
CODE_SAMPLE
            ),
        ]);
    }
}
