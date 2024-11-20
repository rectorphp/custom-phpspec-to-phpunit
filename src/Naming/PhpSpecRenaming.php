<?php

declare(strict_types=1);

namespace Rector\PhpSpecToPHPUnit\Naming;

use PhpParser\Node\Identifier;
use PhpParser\Node\Stmt\Class_;
use PHPStan\Analyser\Scope;
use PHPStan\Reflection\ClassReflection;
use PHPStan\Type\ObjectType;
use Rector\Exception\ShouldNotHappenException;
use Rector\NodeNameResolver\NodeNameResolver;
use Rector\PhpSpecToPHPUnit\NodeAnalyzer\LetClassMethodAnalyzer;
use Rector\PhpSpecToPHPUnit\StringUtils;
use Rector\PhpSpecToPHPUnit\ValueObject\TestedObject;

final class PhpSpecRenaming
{
    /**
     * @var string
     */
    private const SPEC = 'Spec';

    /**
     * @var string[]
     */
    private const METHOD_PREFIXES = ['it_should_have_', 'it_should_be', 'it_should_', 'it_is_', 'it_', 'is_'];

    public function __construct(
        private readonly NodeNameResolver $nodeNameResolver,
        private readonly LetClassMethodAnalyzer $letClassMethodAnalyzer,
    ) {
    }

    public function resolvePHPUnitTestMethodName(string $methodName): ?string
    {
        $unPrefixedMethodName = $this->removeNamePrefixes($methodName);

        // from PhpSpec to PHPUnit method naming convention
        $camelCaseMethodName = StringUtils::underscoreAndHyphenToCamelCase($unPrefixedMethodName);

        // add "test", so PHPUnit runs the method
        if (! \str_starts_with($camelCaseMethodName, 'test')) {
            return 'test' . ucfirst($camelCaseMethodName);
        }

        return null;
    }

    public function createPHPUnitTestClassName(Class_ $class): string
    {
        $classShortName = $this->nodeNameResolver->getShortName($class);

        // 2. change class name
        $newClassName = StringUtils::removeSuffixes($classShortName, [self::SPEC]);
        return $newClassName . 'Test';
    }

    public function resolveTestedObject(Class_ $class): TestedObject
    {
        $className = $this->resolveTestedClassName($class);
        $propertyName = $this->resolveTestedObjectPropertyName($class);
        $testedObjectType = new ObjectType($className);

        $definedMockVariableNames = $this->letClassMethodAnalyzer->resolveDefinedMockVariableNames($class);

        return new TestedObject($className, $propertyName, $testedObjectType, $definedMockVariableNames);
    }

    public function resolveTestedObjectPropertyNameFromScope(Scope $scope): ?string
    {
        $classReflection = $scope->getClassReflection();
        if (! $classReflection instanceof ClassReflection) {
            return null;
        }

        $className = $classReflection->getName();

        /** @var string $shortClassName */
        $shortClassName = StringUtils::after($className, '\\', -1);

        $suffixlessClassName = StringUtils::removeSuffixes($shortClassName, [self::SPEC]);

        return lcfirst($suffixlessClassName);
    }

    private function resolveTestedObjectPropertyName(Class_ $class): string
    {
        // anonymous class?
        if (! $class->name instanceof Identifier) {
            throw new ShouldNotHappenException();
        }

        $shortClassName = $this->nodeNameResolver->getShortName($class);
        $bareClassName = StringUtils::removeSuffixes($shortClassName, [self::SPEC, 'Test']);

        return lcfirst($bareClassName);
    }

    private function resolveTestedClassName(Class_ $class): string
    {
        $className = (string) $this->nodeNameResolver->getName($class);

        $newClassName = StringUtils::removePrefixes($className, ['spec\\']);
        return StringUtils::removeSuffixes($newClassName, [self::SPEC]);
    }

    private function removeNamePrefixes(string $name): string
    {
        $originalName = $name;
        $name = StringUtils::removePrefixes($name, self::METHOD_PREFIXES);

        if ($name === '') {
            return $originalName;
        }

        return $name;
    }
}
