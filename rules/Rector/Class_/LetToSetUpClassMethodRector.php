<?php

declare(strict_types=1);

namespace Rector\PhpSpecToPHPUnit\Rector\Class_;

use PhpParser\Node;
use PhpParser\Node\Expr\Assign;
use PhpParser\Node\Expr\MethodCall;
use PhpParser\Node\Expr\New_;
use PhpParser\Node\Expr\PropertyFetch;
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
    public function let(SomeDependency $someDependency)
    {
        $this->beConstructedWith($someDependency);
    }
}
CODE_SAMPLE
                    ,
                    <<<'CODE_SAMPLE'
use PhpSpec\ObjectBehavior;
use PHPUnit\Framework\MockObject\MockObject;

final class SomeTypeSpec extends ObjectBehavior
{
    private SomeType $someType;

    /**
     * @var MockObject<SomeDependency>
     */
    private MockObject $someDependencyMock;

    protected function setUp(): void
    {
        $this->someDependencyMock = $this->createMock(SomeDependency::class);
        $this->someType = new SomeType($this->someDependencyMock);
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
        $letClassMethod = $node->getMethod(PhpSpecMethodName::LET);
        if (! $letClassMethod instanceof ClassMethod) {
            return null;
        }

        $testedObject = $this->phpSpecRenaming->resolveTestedObject($node);

        $testedObjectProperty = $this->nodeFactory->createPrivatePropertyFromNameAndType(
            $testedObject->getPropertyName(),
            $testedObject->getTestedObjectType()
        );

        $this->changeBeConstructedWithToAnAssign(
            $letClassMethod,
            $testedObject->getTestedObjectType(),
            $testedObject->getPropertyName()
        );

        $letClassMethod->name = new Identifier(MethodName::SET_UP);
        $letClassMethod->returnType = new Identifier('void');
        $letClassMethod->params = [];
        $this->visibilityManipulator->makeProtected($letClassMethod);

        // add as first
        $node->stmts = array_merge([$testedObjectProperty], $node->stmts);

        return $node;
    }

    private function changeBeConstructedWithToAnAssign(
        ClassMethod $letClassMethod,
        ObjectType $testedObjectType,
        string $testedObjectPropertyName
    ): void {
        $this->traverseNodesWithCallable($letClassMethod, function (Node $node) use (
            $testedObjectType,
            $testedObjectPropertyName
        ) {
            if (! $node instanceof MethodCall) {
                return null;
            }

            if (! $this->isName($node->name, PhpSpecMethodName::BE_CONSTRUCTED_WITH)) {
                return null;
            }

            $new = new New_(new FullyQualified($testedObjectType->getClassName()), $node->getArgs());
            $mockPropertyFetch = new PropertyFetch(new Variable('this'), new Identifier($testedObjectPropertyName));

            return new Assign($mockPropertyFetch, $new);
        });
    }
}
