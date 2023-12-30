<?php

declare(strict_types=1);

namespace Rector\PhpSpecToPHPUnit\ValueObject;

use PhpParser\Node\Expr\MethodCall;
use PhpParser\Node\Expr\Variable;
use PhpParser\Node\Stmt\Expression;
use Rector\Core\Exception\ShouldNotHappenException;

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

    public function getMockVariable(): Variable
    {
        /** @var MethodCall $methodCall */
        $methodCall = $this->expression->expr;

        while ($methodCall->var instanceof MethodCall) {
            $methodCall = $methodCall->var;
        }

        if (! $methodCall->var instanceof Variable) {
            throw new ShouldNotHappenException();
        }

        return $methodCall->var;
    }
}
