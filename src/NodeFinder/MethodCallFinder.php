<?php

declare(strict_types=1);

namespace Rector\PhpSpecToPHPUnit\NodeFinder;

use PhpParser\Node;
use PhpParser\Node\Expr\MethodCall;
use PhpParser\Node\Identifier;
use PhpParser\Node\Stmt;
use PhpParser\NodeFinder;

final class MethodCallFinder
{
    public static function findByName(Stmt $stmt, string $desiredMethodName): ?MethodCall
    {
        $nodeFinder = new NodeFinder();

        $foundMethodCall = $nodeFinder->findFirst($stmt, function (Node $node) use ($desiredMethodName): bool {
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
