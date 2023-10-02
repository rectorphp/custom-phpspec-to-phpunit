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
use PhpParser\Node\Name\FullyQualified;
use PhpParser\Node\Stmt\Class_;
use PhpParser\Node\Stmt\ClassMethod;
use PhpParser\Node\Stmt\Expression;
use PHPStan\Type\ObjectType;
use Rector\Core\Exception\ShouldNotHappenException;
use Rector\Core\Rector\AbstractRector;
use Rector\PhpSpecToPHPUnit\LetManipulator;
use Rector\PhpSpecToPHPUnit\Naming\PhpSpecRenaming;
use Rector\PhpSpecToPHPUnit\NodeAnalyzer\PhpSpecBehaviorNodeDetector;
use Rector\PhpSpecToPHPUnit\NodeFactory\SetUpMethodFactory;
use Rector\PHPStanStaticTypeMapper\Enum\TypeKind;
use Rector\StaticTypeMapper\StaticTypeMapper;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;

/**
 * @see \Rector\PhpSpecToPHPUnit\Tests\Rector\Class_\PhpSpecClassToPHPUnitClassRector\PhpSpecClassToPHPUnitClassRectorTest
 */
final class PhpSpecClassToPHPUnitClassRector extends AbstractRector
{
    public function __construct(
        private readonly LetManipulator $letManipulator,
        private readonly PhpSpecRenaming $phpSpecRenaming,
        private readonly PhpSpecBehaviorNodeDetector $phpSpecBehaviorNodeDetector,
        private readonly SetUpMethodFactory $setUpMethodFactory,
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

        // skip already renamed
        /** @var string $className */
        $className = $this->getName($node);
        if (str_ends_with($className, 'Test')) {
            return null;
        }

        $propertyName = $this->phpSpecRenaming->resolveObjectPropertyName($node);

        $phpunitTestClassName = $this->phpSpecRenaming->resolvePHPUnitTestClassName($node);

        // rename class and parent class
        $node->name = new Identifier($phpunitTestClassName);
        $node->extends = new FullyQualified('PHPUnit\Framework\TestCase');

        $testedClass = $this->phpSpecRenaming->resolveTestedClassName($node);
        $testedObjectType = new ObjectType($testedClass);

        $newStmts = [];

        // add default property, if let() method is here
        $letClassMethod = $node->getMethod('let');
        if ($letClassMethod instanceof ClassMethod) {
            $newStmts[] = $this->nodeFactory->createPrivatePropertyFromNameAndType($propertyName, $testedObjectType);
        }

        // add setUp() if completely missing and need
        if ($this->letManipulator->isSetUpClassMethodLetNeeded($node)) {
            $newStmts[] = $this->createSetUpClassMethod($propertyName, $testedObjectType);
        }

        $node->stmts = array_merge($newStmts, $node->stmts);

        return $node;
    }

    public function getRuleDefinition(): RuleDefinition
    {
        return new RuleDefinition('wip', []);
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
