<?php

declare(strict_types=1);

namespace Rector\PhpSpecToPHPUnit\NodeAnalyzer;

use PhpParser\Node\Stmt\Class_;
use PhpParser\Node\Stmt\ClassMethod;
use Rector\Core\ValueObject\MethodName;
use Rector\NodeTypeResolver\Node\AttributeKey;
use Rector\PhpSpecToPHPUnit\Enum\PhpSpecMethodName;
use Rector\PhpSpecToPHPUnit\NodeFinderHelper;

final class LetMethodAnalyzer
{
    public function isSetUpClassMethodLetNeeded(Class_ $class): bool
    {
        // this will be renamed later to setUp() by another rule
        $letClassMethod = $class->getMethod(PhpSpecMethodName::LET);
        if ($letClassMethod instanceof ClassMethod) {
            return false;
        }

        // already has setUp()
        $setUpClassMethod = $class->getMethod(MethodName::SET_UP);
        if ($setUpClassMethod instanceof ClassMethod) {
            return false;
        }

        foreach ($class->getMethods() as $classMethod) {
            // get original class method, in case it was already refactored
            $originalClassMethod = $classMethod->getAttribute(AttributeKey::ORIGINAL_NODE);

            if (! $originalClassMethod->isPublic()) {
                continue;
            }

            if (NodeFinderHelper::hasMethodCallNamed($originalClassMethod, PhpSpecMethodName::BE_CONSTRUCTED_THROUGH)) {
                continue;
            }

            return true;
        }

        return false;
    }
}
