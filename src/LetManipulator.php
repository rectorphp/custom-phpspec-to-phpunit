<?php

declare(strict_types=1);

namespace Rector\PhpSpecToPHPUnit;

use PhpParser\Node;
use PhpParser\Node\Expr\MethodCall;
use PhpParser\Node\Stmt\Class_;
use PhpParser\Node\Stmt\ClassMethod;
use Rector\Core\PhpParser\Node\BetterNodeFinder;
use Rector\Core\ValueObject\MethodName;
use Rector\NodeNameResolver\NodeNameResolver;

final class LetManipulator
{
    public function __construct(
        private readonly BetterNodeFinder $betterNodeFinder,
        private readonly NodeNameResolver $nodeNameResolver
    ) {
    }

    public function isSetUpClassMethodLetNeeded(Class_ $class): bool
    {
        $letClassMethod = $class->getMethod('let');
        if ($letClassMethod instanceof ClassMethod) {
            return false;
        }

        // already has setUp()
        $setUpClassMethod = $class->getMethod(MethodName::SET_UP);
        if ($setUpClassMethod instanceof ClassMethod) {
            return false;
        }

        foreach ($class->getMethods() as $classMethod) {
            $hasBeConstructedThrough = (bool) $this->betterNodeFinder->find(
                (array) $classMethod->stmts,
                function (Node $node): bool {
                    if (! $node instanceof MethodCall) {
                        return false;
                    }

                    return $this->nodeNameResolver->isName($node->name, 'beConstructedThrough');
                }
            );

            if ($hasBeConstructedThrough) {
                continue;
            }

            return true;
        }

        return false;
    }
}
