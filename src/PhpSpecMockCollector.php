<?php

declare(strict_types=1);

namespace Rector\PhpSpecToPHPUnit;

use PhpParser\Node\Expr\Variable;
use PhpParser\Node\Name;
use PhpParser\Node\Param;
use PhpParser\Node\Stmt\Class_;
use PhpParser\Node\Stmt\ClassMethod;
use Rector\Core\Exception\ShouldNotHappenException;
use Rector\NodeNameResolver\NodeNameResolver;
use Rector\PhpSpecToPHPUnit\ValueObject\VariableMock;

final class PhpSpecMockCollector
{
    /**
     * @var array<string, mixed>
     */
    private array $propertyMocksByClass = [];

    public function __construct(
        private readonly NodeNameResolver $nodeNameResolver,
    ) {
    }

    /**
     * @return VariableMock[]
     */
    public function resolveVariableMocksFromClassMethodParams(Class_ $class): array
    {
        $variableMocks = [];

        foreach ($class->getMethods() as $classMethod) {
            if (! $classMethod->isPublic()) {
                continue;
            }

            foreach ($classMethod->params as $param) {
                $variableMocks[] = $this->createVariableMock($classMethod, $param);
            }
        }

        return array_unique($variableMocks);
    }

    public function isVariableMockInProperty(Class_ $class, Variable $variable): bool
    {
        $variableName = $this->nodeNameResolver->getName($variable);
        $className = (string) $this->nodeNameResolver->getName($class);

        return in_array($variableName, $this->propertyMocksByClass[$className] ?? [], true);
    }

    private function createVariableMock(ClassMethod $classMethod, Param $param): VariableMock
    {
        /** @var string $variable */
        $variable = $this->nodeNameResolver->getName($param->var);

        $methodName = $this->nodeNameResolver->getName($classMethod);

        // this should be always typed
        if (! $param->type instanceof Name) {
            throw new ShouldNotHappenException('Param type must be always typed');
        }

        $mockClassName = $param->type->toString();
        return new VariableMock($methodName, $variable, $mockClassName);
    }
}
