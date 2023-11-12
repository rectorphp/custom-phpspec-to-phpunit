<?php

declare(strict_types=1);

namespace Rector\PhpSpecToPHPUnit\Naming;

use PhpParser\Node\Expr\Assign;
use PhpParser\Node\Expr\PropertyFetch;
use PhpParser\Node\Expr\Variable;
use PhpParser\Node\Stmt\Expression;

final class PropertyNameResolver
{
    /**
     * @param Expression[] $expressions
     * @return string[]
     */
    public static function resolveFromPropertyAssigns(array $expressions): array
    {
        $propertyNames = [];

        foreach ($expressions as $stmt) {
            if (! $stmt->expr instanceof Assign) {
                continue;
            }

            $assign = $stmt->expr;
            if (! $assign->var instanceof PropertyFetch) {
                continue;
            }

            $propertyFetch = $assign->var;
            if (! $propertyFetch->var instanceof Variable) {
                continue;
            }

            $propertyNames[] = $propertyFetch->name->toString();
        }

        return $propertyNames;
    }
}
