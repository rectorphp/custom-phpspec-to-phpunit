<?php

declare(strict_types=1);

namespace Rector\PhpSpecToPHPUnit\ValueObject;

use PhpParser\Node\Expr\Variable;
use Rector\Core\Exception\ShouldNotHappenException;

final class MethodNameConsecutiveMethodCalls
{
    /**
     * @param ConsecutiveMethodCall[] $consecutiveMethodCalls
     */
    public function __construct(
        private readonly array $consecutiveMethodCalls
    ) {
    }

    public function getMethodName(): string
    {
        foreach ($this->consecutiveMethodCalls as $consecutiveMethodCall) {
            return $consecutiveMethodCall->getMethodName();
        }

        throw new ShouldNotHappenException();
    }

    /**
     * @return ConsecutiveMethodCall[]
     */
    public function getConsecutiveMethodCalls(): array
    {
        return $this->consecutiveMethodCalls;
    }

    public function getMockVariable(): Variable
    {
        foreach ($this->consecutiveMethodCalls as $consecutiveMethodCall) {
            return new Variable($consecutiveMethodCall->getMockVariableName());
        }

        throw new ShouldNotHappenException();
    }

    public function getFirstStmtKey(): int
    {
        foreach ($this->consecutiveMethodCalls as $consecutiveMethodCall) {
            return $consecutiveMethodCall->getKey();
        }

        throw new ShouldNotHappenException();
    }

    public function getMethodCallCount(): int
    {
        return count($this->consecutiveMethodCalls);
    }
}
