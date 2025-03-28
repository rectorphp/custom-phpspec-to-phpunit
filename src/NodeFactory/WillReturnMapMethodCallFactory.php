<?php

declare(strict_types=1);

namespace Rector\PhpSpecToPHPUnit\NodeFactory;

use PhpParser\Node;
use PhpParser\Node\Arg;
use PhpParser\Node\ArrayItem;
use PhpParser\Node\Expr\Array_;
use PhpParser\Node\Expr\MethodCall;
use PhpParser\Node\Identifier;
use PhpParser\Node\Scalar\String_;
use PhpParser\NodeFinder;
use Rector\NodeNameResolver\NodeNameResolver;
use Rector\PhpSpecToPHPUnit\Enum\PhpSpecMethodName;
use Rector\PhpSpecToPHPUnit\Enum\PHPUnitMethodName;
use Rector\PhpSpecToPHPUnit\ValueObject\MethodNameConsecutiveMethodCalls;

final class WillReturnMapMethodCallFactory
{
    public function __construct(
        private readonly NodeNameResolver $nodeNameResolver,
    ) {
    }

    public function create(MethodNameConsecutiveMethodCalls $methodNameConsecutiveMethodCalls): MethodCall
    {
        $expectsMethodCall = ExpectsCallFactory::createExpectExactlyCall(
            $methodNameConsecutiveMethodCalls->getMethodCallCount(),
            $methodNameConsecutiveMethodCalls->getMockVariable()
        );

        $methodMethodCall = new MethodCall($expectsMethodCall, new Identifier(PHPUnitMethodName::METHOD_), [
            new Arg(new String_($methodNameConsecutiveMethodCalls->getMethodName())),
        ]);

        $consecutiveMapArray = $this->createConsecutiveItemsArray($methodNameConsecutiveMethodCalls);

        return new MethodCall($methodMethodCall, new Identifier(PHPUnitMethodName::WILL_RETURN_MAP), [
            new Arg($consecutiveMapArray),
        ]);
    }

    /**
     * @return Arg[]
     */
    private function resolveInputArgs(MethodCall $methodCall, string $desiredMethodName): array
    {
        $nodeFinder = new NodeFinder();
        $desiredMethodCall = $nodeFinder->findFirst($methodCall, function (Node $node) use ($desiredMethodName): bool {
            if (! $node instanceof MethodCall) {
                return false;
            }

            return $this->nodeNameResolver->isName($node->name, $desiredMethodName);
        });

        if (! $desiredMethodCall instanceof MethodCall) {
            return [];
        }

        return $desiredMethodCall->getArgs();
    }

    /**
     * @param Arg[] $args
     * @return ArrayItem[]
     */
    private function createArrayItemsFromArgs(array $args): array
    {
        $arrayItems = [];
        foreach ($args as $arg) {
            $arrayItems[] = new ArrayItem($arg->value);
        }

        return $arrayItems;
    }

    private function createConsecutiveItemsArray(
        MethodNameConsecutiveMethodCalls $methodNameConsecutiveMethodCalls
    ): Array_ {
        $consecutiveArrayItems = [];

        foreach ($methodNameConsecutiveMethodCalls->getConsecutiveMethodCalls() as $consecutiveMethodCall) {
            $inputArgs = $this->resolveInputArgs(
                $consecutiveMethodCall->getMethodCall(),
                $methodNameConsecutiveMethodCalls->getMethodName()
            );

            $returnArgs = $this->resolveInputArgs(
                $consecutiveMethodCall->getMethodCall(),
                PhpSpecMethodName::SHOULD_RETURN
            );

            if (empty($returnArgs)) {
                $returnArgs = $this->resolveInputArgs(
                    $consecutiveMethodCall->getMethodCall(),
                    PhpSpecMethodName::WILL_RETURN
                );
            }

            $arrayItems = $this->createArrayItemsFromArgs([...$inputArgs, ...$returnArgs]);
            $singleCallArray = new Array_($arrayItems);

            $consecutiveArrayItems[] = new ArrayItem($singleCallArray);
        }

        return new Array_($consecutiveArrayItems);
    }
}
