<?php

declare(strict_types=1);

namespace Rector\PhpSpecToPHPUnit;

use PhpParser\Node;
use PhpParser\Node\Expr\MethodCall;
use PhpParser\Node\Identifier;
use PhpParser\NodeFinder;

final class NodeFinderHelper
{
    public static function hasMethodCallNamed(Node $node, string $methodName): bool
    {
        $nodeFinder = new NodeFinder();

        $foundNode = $nodeFinder->findFirst(
            $node,
            static function (Node $node) use ($methodName): bool {
                if (! $node instanceof MethodCall) {
                    return false;
                }

                if (! $node->name instanceof Identifier) {
                    return false;
                }

                return $node->name->toString() === $methodName;
            }
        );

        return $foundNode instanceof Node;
    }
}
