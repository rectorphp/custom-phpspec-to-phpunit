<?php

declare(strict_types=1);

namespace Rector\PhpSpecToPHPUnit\NodeAnalyzer;

use PhpParser\Node;
use PhpParser\Node\Expr;
use PhpParser\Node\Expr\MethodCall;
use PhpParser\Node\Expr\Variable;
use PhpParser\Node\Stmt;
use PhpParser\Node\Stmt\ClassMethod;
use PhpParser\Node\Stmt\Expression;
use Rector\NodeNameResolver\NodeNameResolver;
use Rector\NodeTypeResolver\Node\AttributeKey;
use Rector\PhpSpecToPHPUnit\Naming\SystemMethodDetector;

final class ConsecutiveMethodCallMatcher
{
    public function __construct(
        private readonly NodeNameResolver $nodeNameResolver
    ) {
    }

    /**
     * @return array<string, array<int, Expression<MethodCall>>>
     */
    public function matchInClassMethod(ClassMethod $classMethod): array
    {
        // nothing to find
        if ($classMethod->stmts === null) {
            return [];
        }

        $consecutiveMockExpectations = [];

        $previousSpecMethodName = null;
        foreach ($classMethod->stmts as $key => $stmt) {
            // is already converted? skip it
            $originalStmt = $stmt->getAttribute(AttributeKey::ORIGINAL_NODE);
            if (! $originalStmt instanceof Node) {
                continue;
            }

            $specMethodName = $this->resolveSpecObjectMethodCallName($stmt);
            if (! is_string($specMethodName)) {
                continue;
            }

            // is spec method call same as previous one?
            if ($specMethodName === $previousSpecMethodName) {
                /** @expr Expression<MethodCall> $stmt */
                $consecutiveMockExpectations[$previousSpecMethodName][$key] = $stmt;
            }

            $previousSpecMethodName = $specMethodName;
        }

        // @todo use value object

        return $consecutiveMockExpectations;
    }

    private function isThisVariable(Expr $expr): bool
    {
        if (! $expr instanceof Variable) {
            return false;
        }

        return $this->nodeNameResolver->isName($expr, 'this');
    }

    private function resolveSpecObjectMethodCallName(Stmt $stmt): ?string
    {
        if (! $stmt instanceof Expression) {
            return null;
        }

        if (! $stmt->expr instanceof MethodCall) {
            return null;
        }

        $methodCall = $stmt->expr;
        if ($this->isThisVariable($methodCall->var)) {
            return null;
        }

        // find first method call
        while ($methodCall->var instanceof MethodCall) {
            $methodCall = $methodCall->var;
        }

        // root expr should be variable
        if (! $methodCall->var instanceof Variable) {
            return null;
        }

        if ($methodCall->name instanceof Expr) {
            return null;
        }

        $methodName = $methodCall->name->toString();

        // is system method? skip it
        if (SystemMethodDetector::detect($methodName)) {
            return null;
        }

        return $methodName;
    }
}
