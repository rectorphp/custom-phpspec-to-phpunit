<?php

declare(strict_types=1);

namespace Rector\PhpSpecToPHPUnit\NodeFactory;

use PhpParser\Node\Arg;
use PhpParser\Node\Expr\Assign;
use PhpParser\Node\Expr\ClassConstFetch;
use PhpParser\Node\Expr\PropertyFetch;
use PhpParser\Node\Expr\Variable;
use PhpParser\Node\Name;
use PhpParser\Node\Param;
use PhpParser\Node\Stmt\Expression;
use Rector\Core\Exception\ShouldNotHappenException;
use Rector\Core\PhpParser\Node\NodeFactory;
use Rector\NodeNameResolver\NodeNameResolver;

final class MockVariableAssignFactory
{
    public function __construct(
        private readonly NodeNameResolver $nodeNameResolver,
        private readonly NodeFactory $nodeFactory,
    ) {
    }

    /**
     * @return Expression<Assign>
     */
    public function createPropertyFetchMockVariableAssign(Param $param, Name $name): Expression
    {
        $variable = $this->nodeNameResolver->getName($param->var);
        if ($variable === null) {
            throw new ShouldNotHappenException();
        }

        $propertyFetch = new PropertyFetch(new Variable('this'), $variable);

        $methodCall = $this->nodeFactory->createLocalMethodCall('createMock');
        $methodCall->args[] = new Arg(new ClassConstFetch($name, 'class'));

        $assign = new Assign($propertyFetch, $methodCall);

        return new Expression($assign);
    }
}
