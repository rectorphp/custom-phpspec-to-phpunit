<?php

declare(strict_types=1);

namespace Rector\PhpSpecToPHPUnit\Rector\Class_;

use PhpParser\Node;
use PhpParser\Node\Expr\Assign;
use PhpParser\Node\Expr\New_;
use PhpParser\Node\Expr\Variable;
use PhpParser\Node\Identifier;
use PhpParser\Node\Name\FullyQualified;
use PhpParser\Node\Stmt\Class_;
use PhpParser\Node\Stmt\ClassMethod;
use PHPStan\Type\ObjectType;
use Rector\Core\Rector\AbstractRector;
use Rector\Core\ValueObject\MethodName;
use Rector\PhpSpecToPHPUnit\Enum\PhpSpecMethodName;
use Rector\PhpSpecToPHPUnit\Naming\PhpSpecRenaming;
use Rector\Privatization\NodeManipulator\VisibilityManipulator;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;

/**
 * @see \Rector\PhpSpecToPHPUnit\Tests\Rector\Class_\LetToSetUpClassMethodRector\LetToSetUpClassMethodRectorTest
 */
final class LetToSetUpClassMethodRector extends AbstractRector
{
    public function __construct(
        private readonly VisibilityManipulator $visibilityManipulator,
        private readonly PhpSpecRenaming $phpSpecRenaming,
    ) {
    }

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
            'Change let() method to setUp() PHPUnit method, including property mock initialization',
            [
                new CodeSample(
                    <<<'CODE_SAMPLE'
use PhpSpec\ObjectBehavior;

final class SomeTypeSpec extends ObjectBehavior
{
    public function let()
    {
    }
}
CODE_SAMPLE
                    ,
                    <<<'CODE_SAMPLE'
use PhpSpec\ObjectBehavior;

final class SomeTypeSpec extends ObjectBehavior
{
    private SomeType $someType;

    protected function setUp(): void
    {
        $this->someType = new SomeType();
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
        $testedObjectPropertyName = $this->phpSpecRenaming->resolveTestedObjectPropertyName($node);

        $testedClass = $this->phpSpecRenaming->resolveTestedClassName($node);
        $testedObjectType = new ObjectType($testedClass);

        $newClasStmts = [];

        // add default property, if let() method is here
        $letClassMethod = $node->getMethod(PhpSpecMethodName::LET);
        if ($letClassMethod instanceof ClassMethod) {
            $newClasStmts[] = $this->nodeFactory->createPrivatePropertyFromNameAndType(
                $testedObjectPropertyName,
                $testedObjectType
            );

            // rename and add void return type
            $letClassMethod->name = new Identifier(MethodName::SET_UP);
            $letClassMethod->returnType = new Identifier('void');

            // change be constructed with to an assign
            $this->traverseNodesWithCallable($letClassMethod, function (Node $node) use (
                $testedObjectType,
                $testedObjectPropertyName
            ) {
                if (! $node instanceof Node\Expr\MethodCall) {
                    return null;
                }

                if (! $this->isName($node->name, PhpSpecMethodName::BE_CONSTRUCTED_WITH)) {
                    return null;
                }

                $new = new New_(new FullyQualified($testedObjectType->getClassName()));
                $new->args = $node->getArgs();

                $mockVariable = new Node\Expr\PropertyFetch(new Variable('this'), new Identifier(
                    $testedObjectPropertyName
                ));
                return new Assign($mockVariable, $new);
            });

            // no params
            $letClassMethod->params = [];
            $this->visibilityManipulator->makeProtected($letClassMethod);
        }

        $node->stmts = array_merge($newClasStmts, $node->stmts);

        return $node;
    }
}
