<?php

declare(strict_types=1);

namespace Rector\PhpSpecToPHPUnit;

use PhpParser\Node\Name;
use PhpParser\Node\Param;
use PhpParser\Node\Stmt\ClassMethod;
use Rector\NodeNameResolver\NodeNameResolver;
use Rector\PhpSpecToPHPUnit\Exception\ShouldNotHappenException;
use Rector\PhpSpecToPHPUnit\ValueObject\ServiceMock;

final class ServiceMockResolver
{
    public function __construct(
        private readonly NodeNameResolver $nodeNameResolver,
    ) {
    }

    /**
     * @return ServiceMock[]
     */
    public function resolveServiceMocksFromClassMethodParams(ClassMethod $classMethod): array
    {
        if (! $classMethod->isPublic()) {
            return [];
        }

        $serviceMocks = [];
        foreach ($classMethod->params as $param) {
            if (! $param->type instanceof Name) {
                continue;
            }

            $serviceMocks[] = $this->createServiceMock($param);
        }

        return array_unique($serviceMocks);
    }

    private function createServiceMock(Param $param): ServiceMock
    {
        /** @var string $variableName */
        $variableName = $this->nodeNameResolver->getName($param->var);

        // this should be always typed
        if (! $param->type instanceof Name) {
            throw new ShouldNotHappenException(sprintf('Param "%s" must be typed', $variableName));
        }

        $mockClassName = $param->type->toString();

        return new ServiceMock($variableName, $mockClassName);
    }
}
