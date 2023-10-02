<?php

declare(strict_types=1);

namespace Rector\PhpSpecToPHPUnit;

use PhpParser\Node;
use PhpParser\Node\Expr\MethodCall;
use PhpParser\NodeFinder;

final class NodeFinderHelper
{
    public static function hasMethodCallNamed(\PhpParser\Node $node, string $methodName): bool
    {
        $nodeFinder = new NodeFinder();
        $foundNode = $nodeFinder->findFirst(
            $node,
            function (Node $node) use ($methodName): bool {
                if (! $node instanceof MethodCall) {
                    return false;
                }

                if (! $node->name instanceof Node\Name) {
                    return false;
                }

                return $node->name->toString() === $methodName;
            }
        );

        return $foundNode instanceof \PhpParser\Node;
    }
}
