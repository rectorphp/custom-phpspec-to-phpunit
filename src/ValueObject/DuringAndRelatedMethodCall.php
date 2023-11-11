<?php

declare(strict_types=1);

namespace Rector\PhpSpecToPHPUnit\ValueObject;

use Nette\Utils\Strings;
use PhpParser\Node\Expr\MethodCall;
use PhpParser\Node\Identifier;
use PhpParser\Node\Scalar\String_;
use Webmozart\Assert\Assert;

final class DuringAndRelatedMethodCall
{
    public function __construct(
        private readonly MethodCall $duringMethodCall,
        private readonly MethodCall $exceptionMethodCall,
    ) {
    }

    public function getDuringMethodCall(): MethodCall
    {
        return $this->duringMethodCall;
    }

    public function getCalledMethodName(): string
    {
        $methodName = $this->duringMethodCall->name;
        Assert::isInstanceOf($methodName, Identifier::class);

        if ($methodName->toString() === 'during') {
            // resolve from argument
            $duringArgs = $this->duringMethodCall->getArgs();
            $firstArg = $duringArgs[0];

            if ($firstArg->value instanceof String_) {
                $methodNameString = $firstArg->value;
                return $methodNameString->value;
            }
        }

        // separate as prefix
        $prefixLessMethodName = Strings::substring($methodName->toString(), 6);
        return lcfirst($prefixLessMethodName);
    }

    public function getExceptionMethodCall(): MethodCall
    {
        return $this->exceptionMethodCall;
    }
}
