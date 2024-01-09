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
    /**
     * @readonly
     * @var \Rector\PhpParser\Node\NodeFactory
     */
    private $nodeFactory;
    /**
     * @readonly
     * @var \Rector\NodeNameResolver\NodeNameResolver
     */
    private $nodeNameResolver;
    /**
     * @readonly
     * @var \Rector\PhpParser\Node\Value\ValueResolver
     */
    private $valueResolver;
    /**
     * @var bool
     */
    private $isBoolAssert = false;

    public function __construct(NodeFactory $nodeFactory, NodeNameResolver $nodeNameResolver, ValueResolver $valueResolver)
    {
        $this->nodeFactory = $nodeFactory;
        $this->nodeNameResolver = $nodeNameResolver;
        $this->valueResolver = $valueResolver;
    }

    /**
     * @param \PhpParser\Node\Expr\PropertyFetch|\PhpParser\Node\Expr\Variable $testedPropertyFetchOrVariable
     */
    public function createAssertMethod(
        string $name,
        Expr $value,
        ?Expr $expected,
        $testedPropertyFetchOrVariable
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

    /**
     * @param \PhpParser\Node\Expr\PropertyFetch|\PhpParser\Node\Expr\Variable $propertyFetchOrVariable
     */
    private function thisToTestedObjectPropertyFetch(Expr $expr, $propertyFetchOrVariable): Expr
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
