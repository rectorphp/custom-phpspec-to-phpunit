<?php

declare(strict_types=1);

namespace Rector\PhpSpecToPHPUnit\Rector\ClassMethod;

use PhpParser\Node;
use PhpParser\Node\Arg;
use PhpParser\Node\Expr\Array_;
use PhpParser\Node\Expr\MethodCall;
use PhpParser\Node\Expr\Variable;
use PhpParser\Node\Stmt\ClassMethod;
use PhpParser\Node\Stmt\Expression;
use Rector\Core\PhpParser\Node\Value\ValueResolver;
use Rector\Core\Rector\AbstractRector;
use Rector\PhpSpecToPHPUnit\Enum\PhpSpecMethodName;
use Rector\PhpSpecToPHPUnit\NodeAnalyzer\DuringAndRelatedMethodCallMatcher;
use Rector\PhpSpecToPHPUnit\NodeAnalyzer\PhpSpecBehaviorNodeDetector;
use Rector\PhpSpecToPHPUnit\ValueObject\DuringAndRelatedMethodCall;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;

/**
 * @see \Rector\PhpSpecToPHPUnit\Tests\Rector\ClassMethod\ShouldThrowAndInstantiationOrderRector\ShouldThrowAndInstantiationOrderRectorTest
 */
final class DuringMethodCallRector extends AbstractRector
{
    public function __construct(
        private readonly PhpSpecBehaviorNodeDetector $phpSpecBehaviorNodeDetector,
        private readonly DuringAndRelatedMethodCallMatcher $duringAndRelatedMethodCallMatcher,
        private readonly ValueResolver $valueResolver,
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
        if (! $this->phpSpecBehaviorNodeDetector->isInPhpSpecBehavior($node)) {
            return null;
        }

        if (! $node->isPublic()) {
            return null;
        }

        // special case, @see https://johannespichler.com/writing-custom-phpspec-matchers/
        if ($this->isNames($node, PhpSpecMethodName::RESERVED_CLASS_METHOD_NAMES)) {
            return null;
        }

        foreach ((array) $node->stmts as $key => $stmt) {
            if (! $stmt instanceof Expression) {
                continue;
            }

            $duringAndRelatedMethodCall = $this->duringAndRelatedMethodCallMatcher->match(
                $stmt,
                PhpSpecMethodName::DURING
            );
            if (! $duringAndRelatedMethodCall instanceof DuringAndRelatedMethodCall) {
                continue;
            }

            $expectExceptionExpression = $this->createExpectExceptionStmt(
                $duringAndRelatedMethodCall->getExceptionMethodCall()
            );
            $objectMethodCallExpression = $this->createObjectMethodCallStmt(
                $duringAndRelatedMethodCall->getDuringMethodCall()
            );

            /** @var Node\Stmt[] $currentStmts */
            $currentStmts = $node->stmts;
            array_splice($currentStmts, $key, 1, [$expectExceptionExpression, $objectMethodCallExpression]);

            // update stmts
            $node->stmts = $currentStmts;

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

    /**
     * @return Expression<MethodCall>
     */
    private function createExpectExceptionStmt(MethodCall $nestedMethodCall): Expression
    {
        $thisExpectExceptionMethodCall = new MethodCall(new Variable('this'), 'expectException');
        $thisExpectExceptionMethodCall->args[] = new Arg($nestedMethodCall->getArgs()[0]->value);

        return new Expression($thisExpectExceptionMethodCall);
    }

    /**
     * @return Expression<MethodCall>
     */
    private function createObjectMethodCallStmt(MethodCall $duringMethodCall): Expression
    {
        $args = $duringMethodCall->getArgs();
        $firstArg = $args[0];

        // include arguments too
        $methodName = $this->valueResolver->getValue($firstArg->value);
        $newArgs = $this->resolveMethodCallArgs($args);

        $objectMethodCall = new MethodCall(new Variable('this'), $methodName, $newArgs);

        return new Expression($objectMethodCall);
    }

    /**
     * @param Arg[] $args
     * @return Arg[]
     */
    private function resolveMethodCallArgs(array $args): array
    {
        if (! isset($args[1])) {
            return [];
        }

        $secondArg = $args[1];
        if (! $secondArg->value instanceof Array_) {
            return [];
        }

        $array = $secondArg->value;
        return $this->nodeFactory->createArgs($array->items);
    }
}
