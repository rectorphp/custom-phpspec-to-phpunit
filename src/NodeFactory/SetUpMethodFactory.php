<?php

declare(strict_types=1);

namespace Rector\PhpSpecToPHPUnit\NodeFactory;

use PhpParser\Node\Identifier;
use PhpParser\Node\Stmt;
use PhpParser\Node\Stmt\Class_;
use PhpParser\Node\Stmt\ClassMethod;

final class SetUpMethodFactory
{
    /**
     * @param Stmt[] $stmts
     */
    public function create(array $stmts): ClassMethod
    {
        $classMethod = new ClassMethod('setUp', [
            'stmts' => $stmts,
        ]);

        $classMethod->flags |= Class_::MODIFIER_PROTECTED;
        $classMethod->returnType = new Identifier('void');

        return $classMethod;
    }
}
