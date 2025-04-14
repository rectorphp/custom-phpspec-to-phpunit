<?php

declare(strict_types=1);

namespace Rector\PhpSpecToPHPUnit;

use PhpParser\Node;
use PhpParser\Node\Expr\PropertyFetch;
use PhpParser\Node\Expr\Variable;
use PhpParser\Node\Stmt\ClassMethod;
use Rector\PhpDocParser\NodeTraverser\SimpleCallableNodeTraverser;

final class MockVariableReplacer
{
    /**
     * @readonly
     */
    private SimpleCallableNodeTraverser $simpleCallableNodeTraverser;
    public function __construct(SimpleCallableNodeTraverser $simpleCallableNodeTraverser)
    {
        $this->simpleCallableNodeTraverser = $simpleCallableNodeTraverser;
    }

    /**
     * @param string[] $mockPropertyNames
     */
    public function replaceVariableMockByProperties(ClassMethod $classMethod, array $mockPropertyNames): void
    {
        $this->simpleCallableNodeTraverser->traverseNodesWithCallable(
            (array) $classMethod->stmts,
            static function (Node $node) use ($mockPropertyNames): ?PropertyFetch {
                if (! $node instanceof Variable) {
                    return null;
                }

                /** @var string $variableName */
                $variableName = $node->name;
                if (! in_array($variableName . 'Mock', $mockPropertyNames, true)) {
                    return null;
                }

                return new PropertyFetch(new Variable('this'), $variableName . 'Mock');
            }
        );
    }
}
