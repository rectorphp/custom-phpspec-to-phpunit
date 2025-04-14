<?php

declare(strict_types=1);

namespace Rector\PhpSpecToPHPUnit\ValueObject;

use PhpParser\Node\Expr\Variable;

final class MethodNameConsecutiveMethodCalls
{
    /**
     * @var ConsecutiveMethodCall[]&non-empty-array
     * @readonly
     */
    private array $consecutiveMethodCalls;
    /**
     * @param ConsecutiveMethodCall[]&non-empty-array $consecutiveMethodCalls
     */
    public function __construct(array $consecutiveMethodCalls)
    {
        $this->consecutiveMethodCalls = $consecutiveMethodCalls;
    }

    public function getMethodName(): string
    {
        foreach ($this->consecutiveMethodCalls as $consecutiveMethodCall) {
            return $consecutiveMethodCall->getMethodName();
        }
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
    }

    public function getFirstStmtKey(): int
    {
        foreach ($this->consecutiveMethodCalls as $consecutiveMethodCall) {
            return $consecutiveMethodCall->getKey();
        }
    }

    public function getMethodCallCount(): int
    {
        return count($this->consecutiveMethodCalls);
    }
}
