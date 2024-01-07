<?php

declare(strict_types=1);

namespace Rector\PhpSpecToPHPUnit\Rector\Expression;

use PhpParser\Comment\Doc;
use PhpParser\Node;
use PhpParser\Node\Expr\MethodCall;
use PhpParser\Node\Expr\PropertyFetch;
use PhpParser\Node\Expr\Variable;
use PhpParser\Node\Identifier;
use PhpParser\Node\Param;
use PhpParser\Node\Scalar\String_;
use PhpParser\Node\Stmt;
use PhpParser\Node\Stmt\Expression;
use PhpParser\Node\Stmt\Nop;
use PHPStan\Analyser\Scope;
use Rector\NodeTypeResolver\Node\AttributeKey;
use Rector\PhpSpecToPHPUnit\Enum\PhpSpecMethodName;
use Rector\PhpSpecToPHPUnit\Naming\PhpSpecRenaming;
use Rector\PhpSpecToPHPUnit\NodeFactory\ArgsFactory;
use Rector\PhpSpecToPHPUnit\NodeFinder\MethodCallFinder;
use Rector\Rector\AbstractRector;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;

/**
 * @see \Rector\PhpSpecToPHPUnit\Tests\Rector\Expression\ShouldNotThrowRector\ShouldNotThrowRectorTest
 */
final class ShouldNotThrowRector extends AbstractRector
{
    public function __construct(
        private readonly PhpSpecRenaming $phpSpecRenaming,
    ) {
    }

    /**
     * @return array<class-string<Node>>
     */
    public function getNodeTypes(): array
    {
        return [Expression::class];
    }

    public function getRuleDefinition(): RuleDefinition
    {
        return new RuleDefinition(
            'Handle shouldNotThrow() expectations',
            [
                new CodeSample(
                    <<<'CODE_SAMPLE'
use PhpSpec\ObjectBehavior;

class ResultSpec extends ObjectBehavior
{
    public function it_is_initializable()
    {
        $this->shouldNotThrow(Exception::class)->during(
            'someMethodCall',
            ['someArguments']
        );
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
        // should not throw an exception
        $this->someMethodCall('someArguments');
    }
}
CODE_SAMPLE
                ),
            ]
        );
    }

    /**
     * @param Expression $node
     * @return array<Stmt>|null
     */
    public function refactor(Node $node): ?array
    {
        $shouldNotThrowMethodCall = MethodCallFinder::findByName($node, PhpSpecMethodName::SHOULD_NOT_THROW);
        if (! $shouldNotThrowMethodCall instanceof MethodCall) {
            return null;
        }

        // find during() method call
        $duringMethodCall = MethodCallFinder::findByName($node, PhpSpecMethodName::DURING);
        if (! $duringMethodCall instanceof MethodCall) {
            return null;
        }

        $scope = $node->getAttribute(AttributeKey::SCOPE);
        if ($scope instanceof Scope) {
            /** @var string $testedObjectPropertyName */
            $testedObjectPropertyName = $this->phpSpecRenaming->resolveTestedObjectPropertyNameFromScope($scope);

            $callerExpr = new PropertyFetch(new Variable('this'), new Identifier($testedObjectPropertyName));
        } else {
            $callerExpr = new Variable('this');
        }

        $duringArgs = $duringMethodCall->getArgs();
        if ($duringArgs === []) {
            return null;
        }

        $firstArg = $duringArgs[0];
        if (! $firstArg->value instanceof String_) {
            return null;
        }

        $string = $firstArg->value;
        $methodName = $string->value;

        $nop = new Nop();
        $nop->setDocComment(new Doc('/** should not throw exception */'));

        $methodCall = new MethodCall($callerExpr, $methodName);
        if (isset($duringArgs[1])) {
            $secondArg = $duringArgs[1];
            $newArgs = ArgsFactory::createArgsFromArgArray($secondArg);

            $methodCall->args = $newArgs;
        }

        $methodCallExpression = new Expression($methodCall);

        return [$nop, $methodCallExpression];
    }
}
