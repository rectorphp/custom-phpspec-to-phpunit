<?php

declare(strict_types=1);

namespace Rector\PhpSpecToPHPUnit\NodeAnalyzer;

use PhpParser\Node\Expr\MethodCall;
use PhpParser\Node\Stmt;
use PhpParser\Node\Stmt\Expression;
use Rector\NodeNameResolver\NodeNameResolver;
use Rector\PhpSpecToPHPUnit\Enum\PhpSpecMethodName;
use Rector\PhpSpecToPHPUnit\ValueObject\DuringAndRelatedMethodCall;

final class DuringAndRelatedMethodCallMatcher
{
    public function __construct(
        private readonly NodeNameResolver $nodeNameResolver,
    ) {
    }

    /**
     * Looks for e.g.:
     *
     * $this->shouldThrow(ValidationException::class)->duringInstantiation();
     *
     * Returns MethodCall:
     *
     * $this->shouldThrow(ValidationException::class)
     */
    public function match(Stmt $stmt, string $duringMethodName): ?DuringAndRelatedMethodCall
    {
        if (! $stmt instanceof Expression) {
            return null;
        }

        if (! $stmt->expr instanceof MethodCall) {
            return null;
        }

        $methodCall = $stmt->expr;

        $methodName = $this->nodeNameResolver->getName($methodCall->name);
        if (! is_string($methodName)) {
            return null;
        }

        if (! str_starts_with($methodName, $duringMethodName)) {
            return null;
        }

        $nestedMethodCall = $methodCall->var;
        if (! $nestedMethodCall instanceof MethodCall) {
            return null;
        }

        if (! $this->nodeNameResolver->isName($nestedMethodCall->name, PhpSpecMethodName::SHOULD_THROW)) {
            return null;
        }

        return new DuringAndRelatedMethodCall($methodCall, $nestedMethodCall);
    }
}
