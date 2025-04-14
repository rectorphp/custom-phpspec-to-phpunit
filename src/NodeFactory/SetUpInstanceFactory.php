<?php

declare(strict_types=1);

namespace Rector\PhpSpecToPHPUnit\NodeFactory;

use PhpParser\Modifiers;
use PhpParser\Node\Expr\Assign;
use PhpParser\Node\Expr\New_;
use PhpParser\Node\Expr\PropertyFetch;
use PhpParser\Node\Expr\Variable;
use PhpParser\Node\Identifier;
use PhpParser\Node\Name\FullyQualified;
use PhpParser\Node\Stmt\ClassMethod;
use PhpParser\Node\Stmt\Expression;
use Rector\PhpSpecToPHPUnit\ValueObject\TestedObject;
use Rector\ValueObject\MethodName;

final class SetUpInstanceFactory
{
    public function createSetUpClassMethod(TestedObject $testedObject): ClassMethod
    {
        $classMethod = new ClassMethod(MethodName::SET_UP);
        $classMethod->returnType = new Identifier('void');
        $classMethod->flags |= Modifiers::PROTECTED;

        $propertyFetch = new PropertyFetch(new Variable('this'), $testedObject->getPropertyName());
        $new = new New_(new FullyQualified($testedObject->getClassName()));

        $classMethod->stmts = [new Expression(new Assign($propertyFetch, $new))];

        return $classMethod;
    }
}
