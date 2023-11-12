<?php

declare(strict_types=1);

namespace Rector\PhpSpecToPHPUnit;

use PhpParser\Node;
use PhpParser\Node\Expr\MethodCall;
use PhpParser\Node\Expr\PropertyFetch;
use PhpParser\Node\Expr\Variable;
use PhpParser\Node\Stmt\ClassMethod;
use Rector\PhpDocParser\NodeTraverser\SimpleCallableNodeTraverser;

final class MockVariableReplacer
{
    public function __construct(
        private readonly SimpleCallableNodeTraverser $simpleCallableNodeTraverser,
    ) {
    }

    /**
     * @param string[] $mockPropertyNames
     */
    public function replaceVariableMockByProperties(ClassMethod $letClassMethod, array $mockPropertyNames): void
    {
        $this->simpleCallableNodeTraverser->traverseNodesWithCallable(
            (array) $letClassMethod->stmts,
            function (Node $node) use ($mockPropertyNames): ?Node {
                if (! $node instanceof MethodCall) {
                    return null;
                }

                if (! $node->var instanceof Variable) {
                    return null;
                }

                /** @var string $variableName */
                $variableName = $node->var->name;
                if (! in_array($variableName . 'Mock', $mockPropertyNames, true)) {
                    return null;
                }

                $node->var = new PropertyFetch(new Variable('this'), $variableName . 'Mock');

                return $node;
            }
        );
    }
}
