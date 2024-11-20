<?php

declare(strict_types=1);

namespace Rector\PhpSpecToPHPUnit\NodeFactory;

use PhpParser\Node\Arg;
use PhpParser\Node\ArrayItem;
use PhpParser\Node\Expr\Array_;

final class ArgsFactory
{
    /**
     * @return Arg[]
     */
    public static function createArgsFromArgArray(Arg $arg): array
    {
        if (! $arg->value instanceof Array_) {
            return [];
        }

        $newArgs = [];

        $array = $arg->value;
        foreach ($array->items as $arrayItem) {
            if (! $arrayItem instanceof ArrayItem) {
                continue;
            }

            $newArgs[] = new Arg($arrayItem->value);
        }

        return $newArgs;
    }
}
