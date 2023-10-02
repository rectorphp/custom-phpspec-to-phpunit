<?php

declare(strict_types=1);

namespace Rector\PhpSpecToPHPUnit\NodeAnalyzer;

use PhpParser\Node;
use PhpParser\Node\Stmt\Class_;
use PhpParser\Node\Stmt\ClassMethod;
use PHPStan\Analyser\Scope;
use PHPStan\Reflection\ClassReflection;
use Rector\NodeTypeResolver\Node\AttributeKey;

final class PhpSpecBehaviorNodeDetector
{
    /**
     * @todo remove method call here, ideally the ClassMethod as well
     */
    public function isInPhpSpecBehavior(Class_|ClassMethod|Node\Expr\MethodCall $node): bool
    {
        if ($node instanceof Class_ && $node->extends instanceof Node\Name) {
            // class type is safer, as scope is not always refreshed
            if ($node->extends->toString() === 'PhpSpec\ObjectBehavior') {
                // @todo cache
                return true;
            }
        }

        $scope = $node->getAttribute(AttributeKey::SCOPE);
        if (! $scope instanceof Scope) {
            return false;
        }

        $classReflection = $scope->getClassReflection();
        if (! $classReflection instanceof ClassReflection) {
            return false;
        }

        return $classReflection->isSubclassOf('PhpSpec\ObjectBehavior');
    }
}
