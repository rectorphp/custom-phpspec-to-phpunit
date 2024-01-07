<?php

declare(strict_types=1);

namespace Rector\PhpSpecToPHPUnit\NodeFactory;

use PhpParser\Node\Arg;
use PhpParser\Node\Expr\Assign;
use PhpParser\Node\Expr\MethodCall;
use PhpParser\Node\Expr\New_;
use PhpParser\Node\Expr\PropertyFetch;
use PhpParser\Node\Expr\StaticCall;
use PhpParser\Node\Expr\Variable;
use PhpParser\Node\Name\FullyQualified;
use Rector\NodeNameResolver\NodeNameResolver;
use Rector\PhpParser\Node\NodeFactory;
use Rector\PhpParser\Node\Value\ValueResolver;
use Rector\PhpSpecToPHPUnit\Enum\PhpSpecMethodName;

final class BeConstructedWithAssignFactory
{
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
     * @readonly
     * @var \Rector\PhpParser\Node\NodeFactory
     */
    private $nodeFactory;
    public function __construct(NodeNameResolver $nodeNameResolver, ValueResolver $valueResolver, NodeFactory $nodeFactory)
    {
        $this->nodeNameResolver = $nodeNameResolver;
        $this->valueResolver = $valueResolver;
        $this->nodeFactory = $nodeFactory;
    }

    /**
     * @param \PhpParser\Node\Expr\PropertyFetch|\PhpParser\Node\Expr\Variable $propertyFetchOrVariable
     */
    public function create(
        MethodCall $methodCall,
        string $testedClass,
        $propertyFetchOrVariable
    ): ?Assign {
        if ($this->nodeNameResolver->isName($methodCall->name, PhpSpecMethodName::BE_CONSTRUCTED_WITH)) {
            $new = new New_(new FullyQualified($testedClass));
            $new->args = $methodCall->args;

            // $mockVariable = new Variable($propertyFetchOrVariable->name);
            return new Assign($propertyFetchOrVariable, $new);
        }

        if ($this->nodeNameResolver->isName($methodCall->name, PhpSpecMethodName::BE_CONSTRUCTED_THROUGH)) {
            if (! isset($methodCall->args[0])) {
                return null;
            }

            if (! $methodCall->args[0] instanceof Arg) {
                return null;
            }

            $methodName = $this->valueResolver->getValue($methodCall->args[0]->value);
            $staticCall = $this->nodeFactory->createStaticCall($testedClass, $methodName);

            $this->moveConstructorArguments($methodCall, $staticCall);

            return new Assign($propertyFetchOrVariable, $staticCall);
        }

        return null;
    }

    private function moveConstructorArguments(MethodCall $methodCall, StaticCall $staticCall): void
    {
        $secondArg = $methodCall->getArgs()[1] ?? null;
        if (! $secondArg instanceof Arg) {
            return;
        }

        $newArgs = ArgsFactory::createArgsFromArgArray($secondArg);
        $staticCall->args = $newArgs;
    }
}
