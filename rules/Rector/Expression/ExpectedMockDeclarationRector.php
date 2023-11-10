<?php

declare(strict_types=1);

namespace Rector\PhpSpecToPHPUnit\Rector\Expression;

use PhpParser\Node;
use PhpParser\Node\Arg;
use PhpParser\Node\Expr;
use PhpParser\Node\Expr\MethodCall;
use PhpParser\Node\Expr\PropertyFetch;
use PhpParser\Node\Expr\StaticCall;
use PhpParser\Node\Expr\Variable;
use PhpParser\Node\Param;
use PhpParser\Node\Stmt\Expression;
use PHPStan\Type\Generic\GenericClassStringType;
use Rector\Core\Rector\AbstractRector;
use Rector\PhpSpecToPHPUnit\Enum\PHPUnitMethodName;
use Rector\PhpSpecToPHPUnit\Naming\SystemMethodDetector;
use Rector\PhpSpecToPHPUnit\NodeFactory\ExpectsCallFactory;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;

/**
 * @see \Rector\PhpSpecToPHPUnit\Tests\Rector\Expression\ExpectedMockDeclarationRector\ExpectedMockDeclarationRectorTest
 */
final class ExpectedMockDeclarationRector extends AbstractRector
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
        // usually long chunk of method call
        if (! $node->expr instanceof MethodCall) {
            return null;
        }

        $firstMethodCall = $node->expr;

        // usually a chain method call
        if (! $firstMethodCall->var instanceof MethodCall) {
            return null;
        }

        // replace method name with expects('...')->method('methodName')
        $hasChanged = false;

        $this->traverseNodesWithCallable($firstMethodCall, function (Node $node) use (&$hasChanged) {
            if (! $node instanceof MethodCall) {
                return null;
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

            $expectsMethodCall = ExpectsCallFactory::createExpectsOnceCall($node->var);
            $methodMethodCall = ExpectsCallFactory::createMethodCall($expectsMethodCall, $methodName);

            $callArgs = $node->getArgs();

            if ($callArgs !== []) {
                return $this->appendWithMethodCall($methodMethodCall, $callArgs[0]->value);
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

    private function appendWithMethodCall(MethodCall $methodCall, Expr $expr): MethodCall
    {
        $withMethodCall = new MethodCall($methodCall, PHPUnitMethodName::WITH);

        if ($expr instanceof StaticCall) {
            /** @var string $className */
            $className = $this->getName($expr->class);

            if (str_ends_with($className, 'Argument')) {
                if ($this->isName($expr->name, 'any')) {
                    // no added value having this method
                    return $methodCall;
                }

                if ($this->isName($expr->name, 'type')) {
                    $expr = $this->createIsTypeOrIsInstanceOf($expr);
                }
            }
        }

        $withMethodCall->args = [new Arg($expr)];

        return $withMethodCall;
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
