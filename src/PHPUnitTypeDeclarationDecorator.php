<?php

declare(strict_types=1);

namespace Rector\PhpSpecToPHPUnit;

use PhpParser\Node\Stmt\ClassMethod;
use PHPUnit\Framework\TestCase;
use Rector\Core\PhpParser\AstResolver;
use Rector\Core\ValueObject\MethodName;
use Rector\Testing\PHPUnit\StaticPHPUnitEnvironment;

/**
 * Decorate setUp() and tearDown() with "void" when local TestClass class uses them
 */
final class PHPUnitTypeDeclarationDecorator
{
    public function __construct(
        private readonly AstResolver $astResolver
    ) {
    }

    public function decorate(ClassMethod $classMethod): void
    {
        // skip test run
        if (StaticPHPUnitEnvironment::isPHPUnitRun()) {
            return;
        }

        $setUpClassMethod = $this->astResolver->resolveClassMethod(TestCase::class, MethodName::SET_UP);
        if (! $setUpClassMethod instanceof ClassMethod) {
            return;
        }

        $classMethod->returnType = $setUpClassMethod->returnType;
    }
}