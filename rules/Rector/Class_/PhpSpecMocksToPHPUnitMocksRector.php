<?php

declare(strict_types=1);

namespace Rector\PhpSpecToPHPUnit\Rector\Class_;

use PhpParser\Node;
use PhpParser\Node\Arg;
use PhpParser\Node\Expr;
use PhpParser\Node\Expr\MethodCall;
use PhpParser\Node\Expr\StaticCall;
use PhpParser\Node\Identifier;
use PhpParser\Node\Name;
use PhpParser\Node\Param;
use PhpParser\Node\Scalar\String_;
use PhpParser\Node\Stmt\Class_;
use PhpParser\Node\Stmt\Expression;
use PHPStan\Type\Generic\GenericClassStringType;
use Rector\Core\Exception\ShouldNotHappenException;
use Rector\Core\Rector\AbstractRector;
use Rector\PhpSpecToPHPUnit\Enum\PhpSpecMethodName;
use Rector\PhpSpecToPHPUnit\NodeAnalyzer\PhpSpecBehaviorNodeDetector;
use Rector\PhpSpecToPHPUnit\NodeFactory\MockCallFactory;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;
use Webmozart\Assert\Assert;

/**
 * @see \Rector\PhpSpecToPHPUnit\Tests\Rector\Class_\PhpSpecMocksToPHPUnitMocksRector\PhpSpecMocksToPHPUnitMocksRectorTest
 */
final class PhpSpecMocksToPHPUnitMocksRector extends AbstractRector
{
    /**
     * @readonly
     * @var \Rector\PhpSpecToPHPUnit\NodeAnalyzer\PhpSpecBehaviorNodeDetector
     */
    private $phpSpecBehaviorNodeDetector;
    /**
     * @readonly
     * @var \Rector\PhpSpecToPHPUnit\NodeFactory\MockCallFactory
     */
    private $mockCallFactory;
    public function __construct(PhpSpecBehaviorNodeDetector $phpSpecBehaviorNodeDetector, MockCallFactory $mockCallFactory)
    {
        $this->phpSpecBehaviorNodeDetector = $phpSpecBehaviorNodeDetector;
        $this->mockCallFactory = $mockCallFactory;
    }
    /**
     * @return array<class-string<Node>>
     */
    public function getNodeTypes(): array
    {
        return [Class_::class, MethodCall::class];
    }

    /**
     * @param Class_|MethodCall $node
     */
    public function refactor(Node $node): ?Node
    {
        if (! $this->phpSpecBehaviorNodeDetector->isInPhpSpecBehavior($node)) {
            return null;
        }

        // @todo separate
        if ($node instanceof Class_) {
            return $this->refactorClass($node);
        }

        return $this->processMethodCall($node);
    }

    public function getRuleDefinition(): RuleDefinition
    {
        return new RuleDefinition('From PhpSpec mock expectations to PHPUnit mock expectations', [
            new CodeSample(
                <<<'CODE_SAMPLE'
use PhpSpec\ObjectBehavior;

class ResultSpec extends ObjectBehavior
{
    public function it_is_initializable()
    {
        $this->run()->shouldBeCalled();
    }
}
CODE_SAMPLE
                ,
                <<<'CODE_SAMPLE'
use PhpSpec\ObjectBehavior;

class ResultSpec extends ObjectBehavior
{
    public function it_is_initializable()
    {
        $this->expects($this->atLeastOnce())->method('run');
    }
}
CODE_SAMPLE
            ),

        ]);
    }

    private function processMethodCall(MethodCall $methodCall): ?MethodCall
    {
        if ($this->isName($methodCall->name, PhpSpecMethodName::SHOULD_NOT_BE_CALLED)) {
            return $this->refactorShouldNotBeCalled($methodCall);
        }

        if (! $this->isName($methodCall->name, PhpSpecMethodName::SHOULD_BE_CALLED)) {
            return null;
        }

        if (! $methodCall->var instanceof MethodCall) {
            throw new ShouldNotHappenException();
        }

        $mockMethodName = $this->getName($methodCall->var->name);
        if ($mockMethodName === null) {
            throw new ShouldNotHappenException();
        }

        $firstArg = $methodCall->var->getArgs()[0] ?? null;

        $expectedArg = $firstArg instanceof Arg ? $firstArg->value : null;

        $methodCall->var->name = new Identifier('expects');
        $thisOnceMethodCall = $this->nodeFactory->createLocalMethodCall('atLeastOnce');
        $methodCall->var->args = [new Arg($thisOnceMethodCall)];

        $methodCall->name = new Identifier('method');
        $methodCall->args = [new Arg(new String_($mockMethodName))];

        if ($expectedArg !== null) {
            return $this->appendWithMethodCall($methodCall, $expectedArg);
        }

        return $methodCall;
    }

    private function appendWithMethodCall(MethodCall $methodCall, Expr $expr): MethodCall
    {
        $withMethodCall = new MethodCall($methodCall, 'with');

        if ($expr instanceof StaticCall) {
            /** @var string $className */
            $className = $this->getName($expr->class);

            if (substr_compare($className, 'Argument', -strlen('Argument')) === 0) {
                if ($this->isName($expr->name, 'any')) {
                    // no added value having this method
                    return $methodCall;
                }

                if ($this->isName($expr->name, 'type')) {
                    $expr = $this->createIsTypeOrIsInstanceOf($expr);
                }
            }
        } else {
            $newExpr = $this->nodeFactory->createLocalMethodCall('equalTo');
            $newExpr->args = [new Arg($expr)];
            $expr = $newExpr;
        }

        $withMethodCall->args = [new Arg($expr)];

        return $withMethodCall;
    }

    private function createIsTypeOrIsInstanceOf(StaticCall $staticCall): MethodCall
    {
        $args = $staticCall->getArgs();
        $firstArg = $args[0];

        $argType = $this->nodeTypeResolver->getType($firstArg->value);

        if ($argType instanceof GenericClassStringType) {
            $methodName = 'isInstanceOf';
        } else {
            $methodName = 'isType';
        }

        return $this->nodeFactory->createLocalMethodCall($methodName, $args);
    }

    private function refactorClass(Class_ $class): Class_
    {
        foreach ($class->getMethods() as $classMethod) {
            // public = tests, protected = internal, private = own (no framework magic)
            if ($classMethod->isPrivate()) {
                continue;
            }

            // remove params and turn them to instances
            $assignExrepssions = [];
            foreach ($classMethod->params as $param) {
                if (! $param->type instanceof Name) {
                    throw new ShouldNotHappenException();
                }

                $createMockCall = $this->mockCallFactory->createCreateMockCall($class, $param, $param->type);
                if ($createMockCall instanceof Expression) {
                    $assignExrepssions[] = $createMockCall;
                }
            }

            // remove all params
            $classMethod->params = [];
            $classMethod->stmts = array_merge($assignExrepssions, (array) $classMethod->stmts);
        }

        return $class;
    }

    private function refactorShouldNotBeCalled(MethodCall $methodCall): MethodCall
    {
        $methodCall->name = new Identifier('expects');
        $thisOnceMethodCall = $this->nodeFactory->createLocalMethodCall('never');
        $methodCall->args = [new Arg($thisOnceMethodCall)];

        // make use of method("name") convention
        if ($methodCall->var instanceof MethodCall) {
            $methodMethodCall = $methodCall->var;
            $mockedMethodName = $this->getName($methodMethodCall->name);
            Assert::string($mockedMethodName);

            $methodMethodCall->args = [new Arg(new String_($mockedMethodName))];
            $methodMethodCall->name = new Identifier('method');
        }

        return $methodCall;
    }
}
