<?php

declare(strict_types=1);

namespace Rector\PhpSpecToPHPUnit\Rector\Expression;

use PhpParser\Node;
use PhpParser\Node\Arg;
use PhpParser\Node\Expr\BinaryOp;
use PhpParser\Node\Expr\Closure;
use PhpParser\Node\Expr\MethodCall;
use PhpParser\Node\Expr\PropertyFetch;
use PhpParser\Node\Expr\StaticCall;
use PhpParser\Node\Expr\Variable;
use PhpParser\Node\Identifier;
use PhpParser\Node\Param;
use PhpParser\Node\Stmt\Expression;
use PhpParser\NodeFinder;
use PhpParser\NodeTraverser;
use PHPStan\Type\Generic\GenericClassStringType;
use Rector\NodeTypeResolver\Node\AttributeKey;
use Rector\PhpSpecToPHPUnit\Enum\PhpSpecMethodName;
use Rector\PhpSpecToPHPUnit\Enum\PHPUnitMethodName;
use Rector\PhpSpecToPHPUnit\Naming\PhpSpecRenaming;
use Rector\PhpSpecToPHPUnit\Naming\SystemMethodDetector;
use Rector\PhpSpecToPHPUnit\NodeFactory\ExpectsCallFactory;
use Rector\PhpSpecToPHPUnit\NodeFactory\WillCallableAssertFactory;
use Rector\PhpSpecToPHPUnit\NodeFinder\MethodCallFinder;
use Rector\Rector\AbstractRector;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;

/**
 * @see \Rector\PhpSpecToPHPUnit\Tests\Rector\Expression\ExpectedMockDeclarationRector\ExpectedMockDeclarationRectorTest
 */
final class ExpectedMockDeclarationRector extends AbstractRector
{
    public function __construct(
        private readonly WillCallableAssertFactory $willCallableAssertFactory,
        private readonly PhpSpecRenaming $phpSpecRenaming,
    ) {
    }

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
        // usually long chunk of method call
        if (! $node->expr instanceof MethodCall) {
            return null;
        }

        $firstMethodCall = $node->expr;

        // usually a chain method call
        if (! $firstMethodCall->var instanceof MethodCall) {
            return null;
        }

        // replace ->method('...') with expects('...')->method('methodName')
        $hasChanged = false;

        // handled in another rule
        $hasShouldNotBeCalled = MethodCallFinder::hasByName($node, PhpSpecMethodName::SHOULD_NOT_BE_CALLED);

        $this->traverseNodesWithCallable($firstMethodCall, function (Node $node) use (
            &$hasChanged,
            $hasShouldNotBeCalled
        ): null|int|MethodCall {
            if (! $node instanceof MethodCall) {
                return null;
            }

            // special case for nested callable
            if ($this->isName($node->name, PHPUnitMethodName::CALLBACK)) {
                return NodeTraverser::STOP_TRAVERSAL;
            }

            // rename method
            if ($this->isName($node->name, PhpSpecMethodName::WILL_THROW)) {
                $node->name = new Identifier(PHPUnitMethodName::WILL_THROW_EXCEPTION);
                return $node;
            }

            // typically the top method call must be on a variable
            if (! $node->var instanceof Variable && ! $node->var instanceof PropertyFetch) {
                return null;
            }

            // already converted
            if (SystemMethodDetector::detect($node->name->toString())) {
                return null;
            }

            $hasChanged = true;

            /** @var string $methodName */
            $methodName = $this->getName($node->name);

            // handled already in another method
            $expectsMethodCall = $hasShouldNotBeCalled ? $node->var : ExpectsCallFactory::createExpectsOnceCall(
                $node->var
            );

            $methodMethodCall = ExpectsCallFactory::createMethodCall($expectsMethodCall, $methodName);

            $callArgs = $node->getArgs();
            if ($callArgs !== []) {
                return $this->appendWithMethodCall($methodMethodCall, $callArgs);
            }

            return $methodMethodCall;
        });

        if (! $hasChanged) {
            return null;
        }

        return $node;
    }

    public function getRuleDefinition(): RuleDefinition
    {
        return new RuleDefinition('From PhpSpec mock expectations to PHPUnit mock expectations', [
            new CodeSample(
                <<<'CODE_SAMPLE'
use PhpSpec\ObjectBehavior;

class ResultSpec extends ObjectBehavior
{
    public function it_returns()
    {
        $this->run()->shouldReturn(1000);
    }
}
CODE_SAMPLE
                ,
                <<<'CODE_SAMPLE'
use PhpSpec\ObjectBehavior;

class ResultSpec extends ObjectBehavior
{
    public function it_returns()
    {
        $this->expects($this->once())->method('run')->willReturn(1000);
    }
}
CODE_SAMPLE
            ),
        ]);
    }

    /**
     * @param Arg[] $args
     */
    private function appendWithMethodCall(MethodCall $methodCall, array $args): MethodCall
    {
        foreach ($args as $arg) {
            // flip $this to tested object property fetch
            if ($arg->value instanceof Variable && $this->isName($arg->value, 'this')) {
                $scope = $arg->getAttribute(AttributeKey::SCOPE);

                /** @var string $testedObjectPropertyName */
                $testedObjectPropertyName = $this->phpSpecRenaming->resolveTestedObjectPropertyNameFromScope($scope);

                $arg->value = new PropertyFetch($arg->value, $testedObjectPropertyName);
                continue;
            }

            if ($arg->value instanceof StaticCall) {
                $staticCall = $arg->value;

                /** @var string $className */
                $className = $this->getName($staticCall->class);

                if (str_ends_with($className, 'Argument')) {
                    if ($this->isName($staticCall->name, 'any')) {
                        // no added value having this method
                        return $methodCall;
                    }

                    if ($this->isName($staticCall->name, 'that')) {
                        // will return callable
                        $expr = $staticCall->getArgs()[0]
                            ->value;

                        // special case for assert in closure - must contain binary ops as copares
                        $nodeFinder = new NodeFinder();
                        if ($expr instanceof Closure && $nodeFinder->findInstanceOf($expr, BinaryOp::class)) {
                            return $this->willCallableAssertFactory->create($methodCall, $expr);
                        }

                        return new MethodCall($methodCall, PHPUnitMethodName::WILL_RETURN, [new Arg($expr)]);
                    }

                    if ($this->isName($staticCall->name, 'type')) {
                        $arg->value = $this->createIsTypeOrIsInstanceOf($staticCall);
                    }
                }
            }
        }

        return new MethodCall($methodCall, PHPUnitMethodName::WITH, $args);
    }

    private function createIsTypeOrIsInstanceOf(StaticCall $staticCall): MethodCall
    {
        $args = $staticCall->getArgs();
        $firstArg = $args[0];

        $argType = $this->nodeTypeResolver->getType($firstArg->value);

        $methodName = $argType instanceof GenericClassStringType ? 'isInstanceOf' : 'isType';
        return $this->nodeFactory->createLocalMethodCall($methodName, $args);
    }
}
