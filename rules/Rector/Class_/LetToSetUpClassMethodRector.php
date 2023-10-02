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
use Rector\PhpSpecToPHPUnit\Enum\PhpSpecMethodName;
use Rector\PhpSpecToPHPUnit\Naming\PhpSpecRenaming;
use Rector\PhpSpecToPHPUnit\NodeAnalyzer\LetMethodAnalyzer;
use Rector\PhpSpecToPHPUnit\NodeAnalyzer\PhpSpecBehaviorNodeDetector;
use Rector\PhpSpecToPHPUnit\NodeFactory\SetUpMethodFactory;
use Rector\PHPStanStaticTypeMapper\Enum\TypeKind;
use Rector\Privatization\NodeManipulator\VisibilityManipulator;
use Rector\StaticTypeMapper\StaticTypeMapper;
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
        private readonly LetMethodAnalyzer $letManipulator,
        private readonly StaticTypeMapper $staticTypeMapper,
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

            // no params
            $letClassMethod->params = [];
            $this->visibilityManipulator->makeProtected($letClassMethod);
        }

        // add setUp() if completely missing and need
        elseif ($this->letManipulator->isSetUpClassMethodLetNeeded($node)) {
            $newClasStmts[] = $this->createSetUpClassMethod($testedObjectPropertyName, $testedObjectType);
        }

        $node->stmts = array_merge($newClasStmts, $node->stmts);

        return $node;
    }

    public function getRuleDefinition(): RuleDefinition
    {
        return new RuleDefinition('Change let() method to setUp() PHPUnit method', [
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
    }
}
CODE_SAMPLE
            ),
        ]);
    }

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
