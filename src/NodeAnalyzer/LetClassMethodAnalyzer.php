<?php

declare(strict_types=1);

namespace Rector\PhpSpecToPHPUnit\NodeAnalyzer;

use PhpParser\Node\Stmt\Class_;
use PhpParser\Node\Stmt\ClassMethod;
use Rector\NodeNameResolver\NodeNameResolver;
use Rector\NodeTypeResolver\Node\AttributeKey;
use Rector\PhpSpecToPHPUnit\Enum\PhpSpecMethodName;

final class LetClassMethodAnalyzer
{
    /**
     * @readonly
     */
    private NodeNameResolver $nodeNameResolver;
    public function __construct(NodeNameResolver $nodeNameResolver)
    {
        $this->nodeNameResolver = $nodeNameResolver;
    }

    /**
     * @return string[]
     */
    public function resolveDefinedMockVariableNames(Class_ $class): array
    {
        // make sure to use original class, as here it might be already changed to setUp() method
        $originalClass = $class->getAttribute(AttributeKey::ORIGINAL_NODE);
        if (! $originalClass instanceof Class_) {
            return [];
        }

        $letClassMethod = $originalClass->getMethod(PhpSpecMethodName::LET);
        if (! $letClassMethod instanceof ClassMethod) {
            return [];
        }

        $variableNames = [];
        foreach ($letClassMethod->params as $param) {
            $variableNames[] = $this->nodeNameResolver->getName($param->var);
        }

        /** @var string[] $variableNames */
        return $variableNames;
    }
}
