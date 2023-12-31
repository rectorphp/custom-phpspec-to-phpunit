<?php

declare(strict_types=1);

namespace Rector\PhpSpecToPHPUnit\NodeFinder;

use PhpParser\Node;
use PhpParser\Node\Expr\MethodCall;
use PhpParser\Node\Identifier;
use PhpParser\NodeFinder;

final class MethodCallFinder
{
    public static function hasByName(\PhpParser\Node $node, string $desiredMethodName): bool
    {
        $foundMethodCall = self::findByName($node, $desiredMethodName);
        return $foundMethodCall instanceof MethodCall;
    }

    public static function findByName(\PhpParser\Node $node, string $desiredMethodName): ?MethodCall
    {
        $nodeFinder = new NodeFinder();

        $foundMethodCall = $nodeFinder->findFirst($node, static function (Node $node) use ($desiredMethodName): bool {
            if (! $node instanceof MethodCall) {
                return false;
            }

            if (! $node->name instanceof Identifier) {
                return false;
            }

            $methodName = $node->name->toString();
            return $methodName === $desiredMethodName;
        });

        if (! $foundMethodCall instanceof MethodCall) {
            return null;
        }

        return $foundMethodCall;
    }
}
