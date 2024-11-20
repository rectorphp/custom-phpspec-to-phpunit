<?php

declare(strict_types=1);

namespace Rector\PhpSpecToPHPUnit\Rector\Class_;

use PhpParser\Modifiers;
use PhpParser\Node;
use PhpParser\Node\Expr\Assign;
use PhpParser\Node\Expr\MethodCall;
use PhpParser\Node\Expr\New_;
use PhpParser\Node\Expr\PropertyFetch;
use PhpParser\Node\Expr\Variable;
use PhpParser\Node\Identifier;
use PhpParser\Node\Name\FullyQualified;
use PhpParser\Node\Param;
use PhpParser\Node\PropertyItem;
use PhpParser\Node\Stmt\Class_;
use PhpParser\Node\Stmt\Expression;
use PhpParser\Node\Stmt\Property;
use PHPStan\Node\ClassMethod;
use PHPStan\Type\ErrorType;
use Rector\Rector\AbstractRector;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;

/**
 * @see \Rector\PhpSpecToPHPUnit\Tests\Rector\Class_\CompleteMissingSetUpPropertyRector\CompleteMissingSetUpPropertyRectorTest
 */
final class CompleteMissingSetUpPropertyRector extends AbstractRector
{
    /**
     * @return array<class-string<Node>>
     */
    public function getNodeTypes(): array
    {
        return [Class_::class];
    }

    public function getRuleDefinition(): RuleDefinition
    {
        return new RuleDefinition(
            'Add missing tested property to setUp() PHPUnit method',
            [
                new CodeSample(
                    <<<'CODE_SAMPLE'
use PHPUnit\Framework\TestCase;

final class SomeTest extends TestCase
{
    public function testSomething()
    {
        $some->call();
    }
}
CODE_SAMPLE
                    ,
                    <<<'CODE_SAMPLE'
use PHPUnit\Framework\TestCase;

final class SomeTest extends TestCase
{
    private Some $some;

    protected function setUp()
    {
        $this->some = new Some();
    }

    public function testSomething()
    {
        $this->some->call();
    }
}
CODE_SAMPLE
                ),
            ]
        );
    }

    /**
     * @param Class_ $node
     */
    public function refactor(Node $node): ?Node
    {
        $requiredPropertyNames = [];

        // 1. iterate class methods
        foreach ($node->getMethods() as $classMethod) {
            if (! $classMethod->isPublic()) {
                continue;
            }

            $this->traverseNodesWithCallable($classMethod, function (Node $node) use (
                &$requiredPropertyNames
            ): ?MethodCall {
                if (! $node instanceof MethodCall) {
                    return null;
                }

                $callerType = $this->getType($node->var);
                if (! $callerType instanceof ErrorType) {
                    return null;
                }

                $variableName = $this->getName($node->var);
                if (! is_string($variableName)) {
                    return null;
                }

                $requiredPropertyNames[] = $variableName;

                // replace with property fetch
                $node->var = new PropertyFetch(new Variable('this'), $variableName);

                return $node;
            });
        }

        if ($requiredPropertyNames === []) {
            return null;
        }

        $requiredPropertyNames = array_unique($requiredPropertyNames);

        $newProperties = [];
        foreach ($requiredPropertyNames as $requiredPropertyName) {
            if ($node->getProperty($requiredPropertyName) instanceof Property) {
                // already exists
                continue;
            }

            $newProperties[] = new Property(Modifiers::PRIVATE, [new PropertyItem($requiredPropertyName)]);
        }

        if ($newProperties === []) {
            return null;
        }

        // 3. add setup method, property and replace variable with a property fetch

        $assignExpressions = [];
        foreach ($requiredPropertyNames as $requiredPropertyName) {
            $assign = new Assign(
                new PropertyFetch(new Variable('this'), $requiredPropertyName),
                // @todo resolve object type from FQN
                new New_(new FullyQualified($requiredPropertyName))
            );

            $assignExpressions[] = new Expression($assign);
        }

        $setUpClassMethod = $node->getMethod('setUp');
        if ($setUpClassMethod instanceof ClassMethod) {
            $setUpClassMethod->stmts = array_merge((array) $setUpClassMethod->stmts, $assignExpressions);
        } else {
            $setUpClassMethod = new Node\Stmt\ClassMethod('setUp');
            $setUpClassMethod->flags = Modifiers::PROTECTED;
            $setUpClassMethod->returnType = new Identifier('void');
            $setUpClassMethod->stmts = $assignExpressions;

            $node->stmts = array_merge([$setUpClassMethod], $node->stmts);
        }

        $node->stmts = array_merge($newProperties, $node->stmts);

        return $node;
    }
}
