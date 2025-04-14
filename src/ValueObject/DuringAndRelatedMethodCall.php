<?php

declare(strict_types=1);

namespace Rector\PhpSpecToPHPUnit\ValueObject;

use Nette\Utils\Strings;
use PhpParser\BuilderFactory;
use PhpParser\Node\Arg;
use PhpParser\Node\Expr\Array_;
use PhpParser\Node\Expr\MethodCall;
use PhpParser\Node\Scalar\String_;
use Rector\PhpSpecToPHPUnit\Enum\PhpSpecMethodName;

final class DuringAndRelatedMethodCall
{
    /**
     * @readonly
     */
    private MethodCall $duringMethodCall;
    /**
     * @readonly
     */
    private MethodCall $exceptionMethodCall;
    public function __construct(MethodCall $duringMethodCall, MethodCall $exceptionMethodCall)
    {
        $this->duringMethodCall = $duringMethodCall;
        $this->exceptionMethodCall = $exceptionMethodCall;
    }

    public function getCalledMethodName(): string
    {
        $duringMethodName = $this->getDuringMethodName();

        if ($duringMethodName === PhpSpecMethodName::DURING) {
            // resolve from argument
            $duringArgs = $this->duringMethodCall->getArgs();
            $firstArg = $duringArgs[0];

            if ($firstArg->value instanceof String_) {
                $methodNameString = $firstArg->value;
                return $methodNameString->value;
            }
        }

        // separate as prefix
        $prefixLessMethodName = Strings::substring($duringMethodName, 6);
        return lcfirst($prefixLessMethodName);
    }

    /**
     * @return Arg[]
     */
    public function getCalledArgs(): array
    {
        // return direct arguments
        if ($this->getDuringMethodName() !== PhpSpecMethodName::DURING) {
            return $this->duringMethodCall->getArgs();
        }

        $args = $this->duringMethodCall->getArgs();
        if (count($args) < 2) {
            return [];
        }

        $secondArg = $args[1];
        if (! $secondArg->value instanceof Array_) {
            return [];
        }

        $builderFactory = new BuilderFactory();

        $array = $secondArg->value;

        $values = [];

        foreach ($array->items as $item) {
            $values[] = $item->value;
        }

        return $builderFactory->args($values);
    }

    public function getExceptionMethodCall(): MethodCall
    {
        return $this->exceptionMethodCall;
    }

    private function getDuringMethodName(): string
    {
        $methodName = $this->duringMethodCall->name;
        return $methodName->toString();
    }
}
