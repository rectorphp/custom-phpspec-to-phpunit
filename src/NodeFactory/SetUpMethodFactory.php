<?php

declare(strict_types=1);

namespace Rector\PhpSpecToPHPUnit\NodeFactory;

use PhpParser\Node\Expr\StaticCall;
use PhpParser\Node\Identifier;
use PhpParser\Node\Name;
use PhpParser\Node\Stmt;
use PhpParser\Node\Stmt\Class_;
use PhpParser\Node\Stmt\ClassMethod;
use PhpParser\Node\Stmt\Expression;
use Rector\Core\ValueObject\MethodName;

final class SetUpMethodFactory
{
    public function create(Stmt $stmt): ClassMethod
    {
        $parentSetUpStaticCall = new StaticCall(new Name('parent'), new Identifier(MethodName::SET_UP));
        $stmts = [new Expression($parentSetUpStaticCall), $stmt];

        $classMethod = new ClassMethod(MethodName::SET_UP, [
            'stmts' => $stmts,
        ]);

        $classMethod->flags |= Class_::MODIFIER_PROTECTED;
        $classMethod->returnType = new Identifier('void');

        return $classMethod;
    }
}
