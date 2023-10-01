<?php

declare(strict_types=1);

namespace Rector\PhpSpecToPHPUnit\Naming;

use PhpParser\Node\Identifier;
use PhpParser\Node\Stmt\Class_;
use Rector\Core\Exception\ShouldNotHappenException;
use Rector\NodeNameResolver\NodeNameResolver;
use Rector\PhpSpecToPHPUnit\StringUtils;
use Webmozart\Assert\Assert;

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
    ) {
    }

    public function resolvePHPUnitTestMethodName(string $methodName): string
    {
        $unPrefixedMethodName = $this->removeNamePrefixes($methodName);

        // from PhpSpec to PHPUnit method naming convention
        $camelCaseMethodName = StringUtils::underscoreAndHyphenToCamelCase($unPrefixedMethodName);

        // add "test", so PHPUnit runs the method
        if (! \str_starts_with($camelCaseMethodName, 'test')) {
            $camelCaseMethodName = 'test' . ucfirst($camelCaseMethodName);
        }

        return $camelCaseMethodName;
    }

    public function resolvePHPUnitTestClassName(Class_ $class): void
    {
        $classShortName = $this->nodeNameResolver->getShortName($class);
        Assert::string($classShortName);

        // 2. change class name
        $newClassName = StringUtils::removeSuffixes($classShortName, [self::SPEC]);
        $newTestClassName = $newClassName . 'Test';

        $class->name = new Identifier($newTestClassName);
    }

    public function resolveObjectPropertyName(Class_ $class): string
    {
        // anonymous class?
        if ($class->name === null) {
            throw new ShouldNotHappenException();
        }

        $shortClassName = $this->nodeNameResolver->getShortName($class);
        $bareClassName = StringUtils::removeSuffixes($shortClassName, [self::SPEC, 'Test']);

        return lcfirst($bareClassName);
    }

    public function resolveTestedClassName(Class_ $class): string
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
