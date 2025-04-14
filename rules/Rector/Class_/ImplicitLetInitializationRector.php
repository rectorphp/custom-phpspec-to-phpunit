<?php

declare(strict_types=1);

namespace Rector\PhpSpecToPHPUnit\Rector\Class_;

use PhpParser\Node;
use PhpParser\Node\Param;
use PhpParser\Node\Stmt\Class_;
use PhpParser\Node\Stmt\ClassMethod;
use PhpParser\Node\Stmt\Property;
use Rector\NodeTypeResolver\Node\AttributeKey;
use Rector\PhpSpecToPHPUnit\Enum\PhpSpecMethodName;
use Rector\PhpSpecToPHPUnit\Naming\PhpSpecRenaming;
use Rector\PhpSpecToPHPUnit\NodeFactory\SetUpInstanceFactory;
use Rector\PhpSpecToPHPUnit\NodeFinder\MethodCallFinder;
use Rector\PhpSpecToPHPUnit\ValueObject\TestedObject;
use Rector\Rector\AbstractRector;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;

/**
 * @see \Rector\PhpSpecToPHPUnit\Tests\Rector\Class_\ImplicitLetInitializationRector\ImplicitLetInitializationRectorTest
 */
final class ImplicitLetInitializationRector extends AbstractRector
{
    public function __construct(
        private readonly PhpSpecRenaming $phpSpecRenaming,
        private readonly SetUpInstanceFactory $setUpInstanceFactory,
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

        // has be constructed through in every public method
        if ($this->hasConstructedThroughInEveryClassMethod($node)) {
            return null;
        }

        $testedObject = $this->phpSpecRenaming->resolveTestedObject($node);

        $testedObjectProperty = $this->createTestedObjectProperty($testedObject);

        $setUpClassMethod = $this->setUpInstanceFactory->createSetUpClassMethod($testedObject);
        $node->stmts = [$testedObjectProperty, $setUpClassMethod, ...(array) $node->stmts];

        return $node;
    }

    private function createTestedObjectProperty(TestedObject $testedObject): Property
    {
        return $this->nodeFactory->createPrivatePropertyFromNameAndType(
            $testedObject->getPropertyName(),
            $testedObject->getTestedObjectType()
        );
    }

    private function hasConstructedThroughInEveryClassMethod(Class_ $class): bool
    {
        foreach ($class->getMethods() as $classMethod) {
            if (! $classMethod->isPublic()) {
                continue;
            }

            if (MethodCallFinder::hasByName($classMethod, PhpSpecMethodName::BE_CONSTRUCTED_WITH)) {
                continue;
            }

            if (MethodCallFinder::hasByName($classMethod, PhpSpecMethodName::BE_CONSTRUCTED_THROUGH)) {
                continue;
            }

            return false;
        }

        return true;
    }
}
