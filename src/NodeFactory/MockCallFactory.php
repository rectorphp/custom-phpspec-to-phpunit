<?php

declare(strict_types=1);

namespace Rector\PhpSpecToPHPUnit\NodeFactory;

use PhpParser\Comment\Doc;
use PhpParser\Node\Arg;
use PhpParser\Node\Expr\Assign;
use PhpParser\Node\Expr\ClassConstFetch;
use PhpParser\Node\Expr\MethodCall;
use PhpParser\Node\Expr\Variable;
use PhpParser\Node\Identifier;
use PhpParser\Node\Name;
use PhpParser\Node\Param;
use PhpParser\Node\Stmt\Class_;
use PhpParser\Node\Stmt\Expression;
use PHPUnit\Framework\MockObject\MockObject;
use Rector\Core\Exception\ShouldNotHappenException;
use Rector\NodeNameResolver\NodeNameResolver;
use Rector\NodeTypeResolver\Node\AttributeKey;
use Rector\PhpSpecToPHPUnit\PhpSpecMockCollector;
use Rector\PhpSpecToPHPUnit\ValueObject\VariableMock;

final class MockCallFactory
{
    public function __construct(
        private readonly MockVariableAssignFactory $mockVariableAssignFactory,
        private readonly NodeNameResolver $nodeNameResolver,
        private readonly PhpSpecMockCollector $phpSpecMockCollector,
    ) {
    }

    /**
     * Variable or property fetch, based on number of present params in whole class
     * @return Expression<Assign>|null
     */
    public function createCreateMockCall(Class_ $class, Param $param, Name $name): ?Expression
    {
        $variableMocks = $this->phpSpecMockCollector->resolveVariableMocksFromClassMethodParams($class);
        $variableName = $this->nodeNameResolver->getName($param->var);

        $variableMock = $this->resolveVariableMock($variableMocks, $variableName);
        if (! $variableMock instanceof VariableMock) {
            return null;
        }

        // single use: "$mock = $this->createMock()"
        if (! $this->phpSpecMockCollector->isVariableMockInProperty($class, $param->var)) {
            return $this->createNewMockVariableAssign($param, $name);
        }

        // $reversedMethodsWithThisMock = array_flip($methodsWithThisMock);

        // first use of many: "$this->mock = $this->createMock()"
        //        if ($reversedMethodsWithThisMock[$methodName] === 0) {
        //            return $this->mockVariableAssignFactory->createPropertyFetchMockVariableAssign($param, $name);
        //        }

        return null;
    }

    private function createNewMockVariableAssign(Param $param, Name $name): Expression
    {
        $methodCall = new MethodCall(new Variable('this'), new Identifier('createMock'));
        $methodCall->args[] = new Arg(new ClassConstFetch($name, 'class'));

        $assign = new Assign($param->var, $methodCall);
        $assignExpression = new Expression($assign);

        // add @var doc comment
        $varDoc = $this->createMockVarDoc($param, $name);
        $assignExpression->setDocComment(new Doc($varDoc));

        return $assignExpression;
    }

    private function createMockVarDoc(Param $param, Name $name): string
    {
        $paramType = (string) $name->getAttribute(AttributeKey::ORIGINAL_NAME, $name);
        $variableName = $this->nodeNameResolver->getName($param->var);

        if ($variableName === null) {
            throw new ShouldNotHappenException();
        }

        return sprintf('/** @var %s|\%s $%s */', $paramType, MockObject::class, $variableName);
    }

    /**
     * @param VariableMock[] $variableMocks
     */
    private function resolveVariableMock(array $variableMocks, string $variableName): ?VariableMock
    {
        foreach ($variableMocks as $variableMock) {
            if ($variableMock->getVariableName() === $variableName) {
                return $variableMock;
            }
        }

        return null;
    }
}
