<?php

declare(strict_types=1);

namespace Rector\PhpSpecToPHPUnit\Naming;

use PhpParser\Node;
use PhpParser\Node\Identifier;
use PhpParser\Node\Name;
use PhpParser\Node\Name\FullyQualified;
use PhpParser\Node\Stmt\Class_;
use PhpParser\Node\Stmt\ClassLike;
use PhpParser\Node\Stmt\ClassMethod;
use PHPUnit\Framework\TestCase;
use Rector\Core\Exception\ShouldNotHappenException;
use Rector\Core\PhpParser\Node\BetterNodeFinder;
use Rector\NodeNameResolver\NodeNameResolver;
use Rector\PhpSpecToPHPUnit\StringUtils;

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
        private readonly BetterNodeFinder $betterNodeFinder
    ) {
    }

    public function renameMethod(ClassMethod $classMethod): void
    {
        if ($classMethod->isPrivate()) {
            return;
        }

        $classMethodName = $this->nodeNameResolver->getName($classMethod);
        $classMethodName = $this->removeNamePrefixes($classMethodName);

        // from PhpSpec to PHPUnit method naming convention
        $classMethodName = StringUtils::underscoreAndHyphenToCamelCase($classMethodName);

        // add "test", so PHPUnit runs the method
        if (! \str_starts_with($classMethodName, 'test')) {
            $classMethodName = 'test' . ucfirst($classMethodName);
        }

        $classMethod->name = new Identifier($classMethodName);
    }

    public function renameExtends(Class_ $class): void
    {
        $class->extends = new FullyQualified(TestCase::class);
    }

    public function renameClass(Class_ $class): void
    {
        $classShortName = $this->nodeNameResolver->getShortName($class);

        // anonymous class?
        if ($classShortName === '') {
            throw new ShouldNotHappenException();
        }

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

    public function resolveTestedClass(Node $node): string
    {
        if ($node instanceof ClassLike) {
            $className = (string) $this->nodeNameResolver->getName($node);
        } else {
            // @todo
            $classLike = $this->betterNodeFinder->findParentType($node, ClassLike::class);
            if (! $classLike instanceof ClassLike) {
                throw new ShouldNotHappenException();
            }

            $className = (string) $this->nodeNameResolver->getName($classLike);
        }

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
