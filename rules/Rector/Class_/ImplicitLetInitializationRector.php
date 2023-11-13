<?php

declare(strict_types=1);

namespace Rector\PhpSpecToPHPUnit\Rector\Class_;

use PhpParser\Node;
use PhpParser\Node\Expr\Assign;
use PhpParser\Node\Expr\New_;
use PhpParser\Node\Expr\PropertyFetch;
use PhpParser\Node\Expr\Variable;
use PhpParser\Node\Identifier;
use PhpParser\Node\Name\FullyQualified;
use PhpParser\Node\Param;
use PhpParser\Node\Stmt\Class_;
use PhpParser\Node\Stmt\ClassMethod;
use PhpParser\Node\Stmt\Expression;
use PhpParser\Node\Stmt\Property;
use Rector\Core\Rector\AbstractRector;
use Rector\Core\ValueObject\MethodName;
use Rector\NodeTypeResolver\Node\AttributeKey;
use Rector\PhpSpecToPHPUnit\Enum\PhpSpecMethodName;
use Rector\PhpSpecToPHPUnit\Naming\PhpSpecRenaming;
use Rector\PhpSpecToPHPUnit\ValueObject\TestedObject;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;

/**
 * @see \Rector\PhpSpecToPHPUnit\Tests\Rector\Class_\ImplicitLetInitializationRector\ImplicitLetInitializationRectorTest
 */
final class ImplicitLetInitializationRector extends AbstractRector
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
        return [Class_::class];
    }

    public function getRuleDefinition(): RuleDefinition
    {
        return new RuleDefinition(
            'Add implicit object property to setUp() PHPUnit method',
            [
                new CodeSample(
                    <<<'CODE_SAMPLE'
use PhpSpec\ObjectBehavior;

final class SomeTypeSpec extends ObjectBehavior
{
    public function let()
    {
        $this->run();
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

    public function let()
    {
        $this->someType->run();
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
        // just to be sure the method is there
        $originalClass = $node->getAttribute(AttributeKey::ORIGINAL_NODE);

        $letClassMethod = $originalClass->getMethod(PhpSpecMethodName::LET);
        if ($letClassMethod instanceof ClassMethod) {
            return null;
        }

        $testedObject = $this->phpSpecRenaming->resolveTestedObject($node);

        $testedObjectProperty = $this->createTestedObjectProperty($testedObject);

        $setUpClassMethod = $this->createSetUpClassMethod($testedObject);
        $node->stmts = array_merge([$testedObjectProperty, $setUpClassMethod], (array) $node->stmts);

        return $node;
    }

    private function createSetUpClassMethod(TestedObject $testedObject): ClassMethod
    {
        $classMethod = new ClassMethod(MethodName::SET_UP);
        $classMethod->returnType = new Identifier('void');
        $classMethod->flags |= Class_::MODIFIER_PROTECTED;

        $propertyFetch = new PropertyFetch(new Variable('this'), $testedObject->getPropertyName());
        $new_ = new New_(new FullyQualified($testedObject->getClassName()));

        $classMethod->stmts = [new Expression(new Assign($propertyFetch, $new_))];

        return $classMethod;
    }

    private function createTestedObjectProperty(TestedObject $testedObject): Property
    {
        return $this->nodeFactory->createPrivatePropertyFromNameAndType(
            $testedObject->getPropertyName(),
            $testedObject->getTestedObjectType()
        );
    }
}
