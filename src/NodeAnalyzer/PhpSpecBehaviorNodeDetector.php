<?php

declare(strict_types=1);

namespace Rector\PhpSpecToPHPUnit\NodeAnalyzer;

use PhpParser\Node\Expr\MethodCall;
use PhpParser\Node\Stmt\Class_;
use PhpParser\Node\Stmt\ClassLike;
use PhpParser\Node\Stmt\ClassMethod;
use PHPStan\Analyser\Scope;
use PHPStan\Reflection\ClassReflection;
use PHPStan\Type\ObjectType;
use Rector\NodeTypeResolver\Node\AttributeKey;
use Rector\NodeTypeResolver\NodeTypeResolver;

final class PhpSpecBehaviorNodeDetector
{
    public function __construct(
        private readonly NodeTypeResolver $nodeTypeResolver,
    ) {
    }

    public function isInPhpSpecBehavior(Class_|ClassMethod|MethodCall $node): bool
    {
        if ($node instanceof ClassLike) {
            return $this->nodeTypeResolver->isObjectType($node, new ObjectType('PhpSpec\ObjectBehavior'));
        }

        $scope = $node->getAttribute(AttributeKey::SCOPE);
        if (! $scope instanceof Scope) {
            return false;
        }

        return $this->isScopeInsideObjectBehaviorClass($scope);
    }

    private function isScopeInsideObjectBehaviorClass(Scope $scope): bool
    {
        $classReflection = $scope->getClassReflection();
        if (! $classReflection instanceof ClassReflection) {
            return false;
        }

        return $classReflection->isSubclassOf('PhpSpec\ObjectBehavior');
    }
}
