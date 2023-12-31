<?php

declare(strict_types=1);

namespace Rector\PhpSpecToPHPUnit\NodeAnalyzer;

use PhpParser\Node;
use PhpParser\Node\Expr;
use PhpParser\Node\Expr\MethodCall;
use PhpParser\Node\Expr\Variable;
use PhpParser\Node\Stmt\ClassMethod;
use PhpParser\Node\Stmt\Expression;
use Rector\NodeNameResolver\NodeNameResolver;
use Rector\NodeTypeResolver\Node\AttributeKey;
use Rector\PhpSpecToPHPUnit\Naming\SystemMethodDetector;
use Rector\PhpSpecToPHPUnit\ValueObject\ConsecutiveMethodCall;
use Rector\PhpSpecToPHPUnit\ValueObject\MethodNameConsecutiveMethodCalls;
use Rector\PhpSpecToPHPUnit\ValueObject\VariableNameAndMethodName;

final class ConsecutiveMethodCallMatcher
{
    public function __construct(
        private readonly NodeNameResolver $nodeNameResolver
    ) {
    }

    /**
     * @return MethodNameConsecutiveMethodCalls[]
     */
    public function matchInClassMethod(ClassMethod $classMethod): array
    {
        // nothing to find
        if ($classMethod->stmts === null) {
            return [];
        }

        $consecutiveMockExpectations = [];

        foreach ($classMethod->stmts as $key => $stmt) {
            if (! $stmt instanceof Expression) {
                continue;
            }

            if (! $stmt->expr instanceof MethodCall) {
                continue;
            }

            // is already converted in another rule? skip it
            $originalStmt = $stmt->getAttribute(AttributeKey::ORIGINAL_NODE);
            if (! $originalStmt instanceof Node) {
                continue;
            }

            $variableNameAndMethodName = $this->resolveSpecObjectMethodCallName($stmt);
            if (! $variableNameAndMethodName instanceof VariableNameAndMethodName) {
                continue;
            }

            $consecutiveMockExpectations[$variableNameAndMethodName->getHash()][] = new ConsecutiveMethodCall(
                $key,
                $variableNameAndMethodName->getVariableName(),
                $variableNameAndMethodName->getMethodName(),
                $stmt->expr
            );
        }

        return $this->createMethodNameConsecutiveMethodCalls($consecutiveMockExpectations);
    }

    private function isThisVariable(Expr $expr): bool
    {
        if (! $expr instanceof Variable) {
            return false;
        }

        return $this->nodeNameResolver->isName($expr, 'this');
    }

    private function resolveSpecObjectMethodCallName(Expression $expression): ?VariableNameAndMethodName
    {
        if (! $expression->expr instanceof MethodCall) {
            return null;
        }

        $methodCall = $expression->expr;
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

        $variableName = $this->nodeNameResolver->getName($methodCall->var);
        if (! is_string($variableName)) {
            return null;
        }

        return new VariableNameAndMethodName($variableName, $methodName);
    }

    /**
     * @param array<string, ConsecutiveMethodCall[]> $consecutiveMockExpectations
     * @return MethodNameConsecutiveMethodCalls[]
     */
    private function createMethodNameConsecutiveMethodCalls(array $consecutiveMockExpectations): array
    {
        $methodNameConsecutiveMethodCalls = [];

        foreach ($consecutiveMockExpectations as $hash => $consecutiveMethodCalls) {
            if (count($consecutiveMethodCalls) < 2) {
                // keep only consecutive calls, at least 2 calls of same named method
                continue;
            }

            $methodNameConsecutiveMethodCalls[] = new MethodNameConsecutiveMethodCalls($consecutiveMethodCalls);
        }

        return $methodNameConsecutiveMethodCalls;
    }
}
