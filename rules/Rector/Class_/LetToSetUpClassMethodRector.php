<?php

declare(strict_types=1);

namespace Rector\PhpSpecToPHPUnit\Rector\Class_;

use PhpParser\Node;
use PhpParser\Node\Expr\Assign;
use PhpParser\Node\Expr\New_;
use PhpParser\Node\Expr\PropertyFetch;
use PhpParser\Node\Expr\Variable;
use PhpParser\Node\Identifier;
use PhpParser\Node\Name;
use PhpParser\Node\Stmt\Class_;
use PhpParser\Node\Stmt\ClassMethod;
use PhpParser\Node\Stmt\Expression;
use PHPStan\Type\ObjectType;
use Rector\Core\Exception\ShouldNotHappenException;
use Rector\Core\Rector\AbstractRector;
use Rector\Core\ValueObject\MethodName;
use Rector\PhpSpecToPHPUnit\Naming\PhpSpecRenaming;
use Rector\PhpSpecToPHPUnit\NodeAnalyzer\PhpSpecBehaviorNodeDetector;
use Rector\PhpSpecToPHPUnit\NodeFactory\SetUpMethodFactory;
use Rector\PHPStanStaticTypeMapper\Enum\TypeKind;
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
        private readonly PhpSpecBehaviorNodeDetector $phpSpecBehaviorNodeDetector,
        private readonly PhpSpecRenaming $phpSpecRenaming,
        private readonly SetUpMethodFactory $setUpMethodFactory,
    ) {
    }

    /**
     * @return array<class-string<Node>>
     */
    public function getNodeTypes(): array
    {
        return [Class_::class];
    }

    /**
     * @param Class_ $node
     */
    public function refactor(Node $node): ?Node
    {
        if (! $this->phpSpecBehaviorNodeDetector->isInPhpSpecBehavior($node)) {
            return null;
        }

        $letClassMethod = $node->getMethod('let');
        if (! $letClassMethod instanceof ClassMethod) {
            return null;
        }

        $letClassMethod->name = new Identifier(MethodName::SET_UP);
        $letClassMethod->returnType = new Identifier('void');
        $this->visibilityManipulator->makeProtected($letClassMethod);

        $testedObjectPropertyName = $this->phpSpecRenaming->resolveTestedObjectPropertyName($node);

        // add default property, if let() method is here
        $letClassMethod = $node->getMethod('let');
        if ($letClassMethod instanceof ClassMethod) {
            $newStmts[] = $this->nodeFactory->createPrivatePropertyFromNameAndType(
                $testedObjectPropertyName,
                $testedObjectType
            );
        }

        // add setUp() if completely missing and need
        if ($this->letManipulator->isSetUpClassMethodLetNeeded($node)) {
            $newStmts[] = $this->createSetUpClassMethod($testedObjectPropertyName, $testedObjectType);
        }

        return $node;
    }

    public function getRuleDefinition(): RuleDefinition
    {
        return new RuleDefinition('Change let() method to setUp() PHPUnit method', [
            new CodeSample(
                <<<'CODE_SAMPLE'
use PhpSpec\ObjectBehavior;

final class LetGoLetMethods extends ObjectBehavior
{
    public function let()
    {
    }
}
CODE_SAMPLE
                ,
                <<<'CODE_SAMPLE'
use PhpSpec\ObjectBehavior;

final class LetGoLetMethods extends ObjectBehavior
{
    private TestedType $testedType;

    protected function setUp(): void
    {
    }
}
CODE_SAMPLE
            ),
        ]);
    }

    /**
     * @todo move to setUp() method
     */
    private function createSetUpClassMethod(string $propertyName, ObjectType $testedObjectType): ClassMethod
    {
        $propertyFetch = new PropertyFetch(new Variable('this'), $propertyName);

        $testedObjectType = $this->staticTypeMapper->mapPHPStanTypeToPhpParserNode(
            $testedObjectType,
            TypeKind::RETURN
        );
        if (! $testedObjectType instanceof Name) {
            throw new ShouldNotHappenException();
        }

        $new = new New_($testedObjectType);
        $assign = new Assign($propertyFetch, $new);

        $assignExpression = new Expression($assign);
        return $this->setUpMethodFactory->create($assignExpression);
    }
}
