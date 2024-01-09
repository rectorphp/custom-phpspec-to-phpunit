<?php

declare(strict_types=1);

namespace Rector\PhpSpecToPHPUnit\NodeFactory;

use PhpParser\Node\Arg;
use PhpParser\Node\Expr;
use PhpParser\Node\Expr\MethodCall;
use PhpParser\Node\Expr\PropertyFetch;
use PhpParser\Node\Expr\Variable;
use Rector\NodeNameResolver\NodeNameResolver;
use Rector\PhpParser\Node\NodeFactory;
use Rector\PhpParser\Node\Value\ValueResolver;

final class AssertMethodCallFactory
{
    private bool $isBoolAssert = false;

    public function __construct(
        private readonly NodeFactory $nodeFactory,
        private readonly NodeNameResolver $nodeNameResolver,
        private readonly ValueResolver $valueResolver
    ) {
    }

    public function createAssertMethod(
        string $name,
        Expr $value,
        ?Expr $expected,
        PropertyFetch|Variable $testedPropertyFetchOrVariable
    ): MethodCall {
        $this->isBoolAssert = false;

        $isAssertNull = $expected instanceof Expr && $this->valueResolver->isNull($expected);

        if ($expected instanceof Expr) {
            $name = $isAssertNull ? 'assertNull' : $this->resolveBoolMethodName($name, $expected);
        }

        $assetMethodCall = $this->nodeFactory->createMethodCall('this', $name);

        if (! $this->isBoolAssert && $expected instanceof Expr && ! $isAssertNull) {
            $assetMethodCall->args[] = new Arg($this->thisToTestedObjectPropertyFetch(
                $expected,
                $testedPropertyFetchOrVariable
            ));
        }

        $assetMethodCall->args[] = new Arg($this->thisToTestedObjectPropertyFetch(
            $value,
            $testedPropertyFetchOrVariable
        ));

        return $assetMethodCall;
    }

    private function resolveBoolMethodName(string $name, Expr $expr): string
    {
        if (! $this->valueResolver->isTrueOrFalse($expr)) {
            return $name;
        }

        $isFalse = $this->valueResolver->isFalse($expr);
        if ($name === 'assertSame') {
            $this->isBoolAssert = true;
            return $isFalse ? 'assertFalse' : 'assertTrue';
        }

        if ($name === 'assertNotSame') {
            $this->isBoolAssert = true;
            return $isFalse ? 'assertNotFalse' : 'assertNotTrue';
        }

        return $name;
    }

    private function thisToTestedObjectPropertyFetch(Expr $expr, PropertyFetch|Variable $propertyFetchOrVariable): Expr
    {
        if (! $expr instanceof Variable) {
            return $expr;
        }

        if (! $this->nodeNameResolver->isName($expr, 'this')) {
            return $expr;
        }

        return $propertyFetchOrVariable;
    }
}
