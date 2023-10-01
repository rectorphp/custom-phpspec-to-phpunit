<?php

declare(strict_types=1);

namespace Rector\PhpSpecToPHPUnit\NodeAnalyzer;

use PhpParser\Node;
use PHPStan\Analyser\Scope;
use PHPStan\Reflection\ClassReflection;
use Rector\NodeTypeResolver\Node\AttributeKey;

final class PhpSpecBehaviorNodeDetector
{
    public function isInPhpSpecBehavior(Node $node): bool
    {
        if ($node instanceof Node\Stmt\Class_ && $node->extends instanceof Node\Name) {
            // class type is safer, as scope is not always refreshed
            return $node->extends->toString() === 'PhpSpec\ObjectBehavior';
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
