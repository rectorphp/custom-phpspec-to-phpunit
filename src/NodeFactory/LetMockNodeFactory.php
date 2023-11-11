<?php

declare(strict_types=1);

namespace Rector\PhpSpecToPHPUnit\NodeFactory;

use PhpParser\Node\Expr\Assign;
use PhpParser\Node\Expr\ClassConstFetch;
use PhpParser\Node\Expr\PropertyFetch;
use PhpParser\Node\Expr\Variable;
use PhpParser\Node\Identifier;
use PhpParser\Node\Name;
use PhpParser\Node\Name\FullyQualified;
use PhpParser\Node\Param;
use PhpParser\Node\Stmt\Expression;
use PhpParser\Node\Stmt\Property;
use PHPStan\Type\ObjectType;
use PHPUnit\Framework\MockObject\MockObject;
use Rector\Core\Exception\ShouldNotHappenException;
use Rector\Core\PhpParser\Node\NodeFactory;
use Rector\NodeNameResolver\NodeNameResolver;
use Rector\PhpSpecToPHPUnit\DocFactory;
use Rector\PhpSpecToPHPUnit\ValueObject\ServiceMock;

final class LetMockNodeFactory
{
    public function __construct(
        private readonly NodeFactory $nodeFactory,
        private readonly NodeNameResolver $nodeNameResolver,
    ) {
    }

    /**
     * @param Param[] $params
     * @return Property[]
     */
    public function createMockProperties(array $params): array
    {
        $properties = [];

        foreach ($params as $param) {
            $parameterName = $this->createMockVariableName($param);
            $mockProperty = $this->crateMockProperty($parameterName);

            if (! $param->type instanceof Name) {
                throw new ShouldNotHappenException();
            }

            $mockedClass = $param->type->toString();

            // add docblock
            $propertyDoc = DocFactory::createForMockProperty(new ServiceMock($parameterName, $mockedClass));
            $mockProperty->setDocComment($propertyDoc);

            $properties[] = $mockProperty;
        }

        return $properties;
    }

    /**
     * @param Param[] $params
     * @return array<Expression<Assign>>
     */
    public function createMockPropertyAssignExpressions(array $params): array
    {
        $assignExpressions = [];

        foreach ($params as $param) {
            $parameterName = $this->createMockVariableName($param);
            if (! $param->type instanceof Name) {
                throw new ShouldNotHappenException();
            }

            $mockPropertyFetch = new PropertyFetch(new Variable('this'), new Identifier($parameterName));

            $mockClassName = $param->type->toString();
            $createMockMethodCall = $this->nodeFactory->createMethodCall('this', 'createMock', [
                new ClassConstFetch(new FullyQualified($mockClassName), 'class'),
            ]);

            $assign = new Assign($mockPropertyFetch, $createMockMethodCall);
            $assignExpressions[] = new Expression($assign);
        }

        return $assignExpressions;
    }

    private function crateMockProperty(string $parameterName): Property
    {
        $paramType = new ObjectType(MockObject::class);

        return $this->nodeFactory->createPrivatePropertyFromNameAndType($parameterName, $paramType);
    }

    private function createMockVariableName(Param $param): string
    {
        $paramName = $this->nodeNameResolver->getName($param->var);
        return $paramName . 'Mock';
    }
}
