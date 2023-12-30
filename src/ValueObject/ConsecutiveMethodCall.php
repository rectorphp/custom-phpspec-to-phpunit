<?php

declare(strict_types=1);

namespace Rector\PhpSpecToPHPUnit\ValueObject;

use PhpParser\Node\Expr\MethodCall;
use PhpParser\Node\Stmt\Expression;

final class ConsecutiveMethodCall
{
    /**
     * @param Expression<MethodCall> $expression
     */
    public function __construct(
        private readonly int $key,
        private readonly string $methodName,
        private readonly Expression $expression
    ) {
    }

    public function getKey(): int
    {
        return $this->key;
    }

    public function getMethodName(): string
    {
        return $this->methodName;
    }

    /**
     * @return Expression<MethodCall>
     */
    public function getExpression(): Expression
    {
        return $this->expression;
    }
}
