<?php

declare(strict_types=1);

namespace Rector\PhpSpecToPHPUnit\Rector\Class_;

use PhpParser\Node;
use PhpParser\Node\Expr\Variable;
use PhpParser\Node\Param;
use PhpParser\Node\Stmt\Class_;
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

            $this->traverseNodesWithCallable($classMethod, function (\PhpParser\Node $node) use (
                &$requiredPropertyNames
            ) {
                if (! $node instanceof Node\Expr\MethodCall) {
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
                $node->var = new Node\Expr\PropertyFetch(new Variable('this'), $variableName);

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

            $newProperties[] = new Property(Class_::MODIFIER_PRIVATE, [
                new Node\Stmt\PropertyProperty($requiredPropertyName),
            ]);
        }

        if ($newProperties === []) {
            return null;
        }

        // 3. add setup method, property and replace variable with a property fetch

        $setUpClassMethod = $node->getMethod('setUp');
        if ($setUpClassMethod instanceof ClassMethod) {
            $setUpClassMethod->stmts[] = new Node\Stmt\Expression(new Node\Expr\Assign(
                new Node\Expr\PropertyFetch(new Variable('this'), $requiredPropertyName),
                // @todo resolve object type from FQN
                new Node\Expr\New_(new Node\Name($requiredPropertyName))
            ));
        } else {
            $setUpClassMethod = new Node\Stmt\ClassMethod('setUp');
            $setUpClassMethod->flags = Class_::MODIFIER_PROTECTED;
            $setUpClassMethod->returnType = new Node\Identifier('void');

            $setUpClassMethod->stmts[] = new Node\Stmt\Expression(new Node\Expr\Assign(
                new Node\Expr\PropertyFetch(new Variable('this'), $requiredPropertyName),
                // @todo resolve object type from FQN
                new Node\Expr\New_(new Node\Name($requiredPropertyName))
            ));

            $node->stmts = array_merge([$setUpClassMethod], $node->stmts);
        }

        $node->stmts = array_merge($newProperties, $node->stmts);

        return $node;
    }
}
