<?php

declare(strict_types=1);

namespace Rector\PhpSpecToPHPUnit\Rector\Class_;

use PhpParser\Node;
use PhpParser\Node\Arg;
use PhpParser\Node\Expr\Assign;
use PhpParser\Node\Expr\MethodCall;
use PhpParser\Node\Expr\New_;
use PhpParser\Node\Expr\PropertyFetch;
use PhpParser\Node\Expr\Variable;
use PhpParser\Node\Name;
use PhpParser\Node\Stmt;
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
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;

/**
 * @see \Rector\PhpSpecToPHPUnit\Tests\Rector\Variable\PhpSpecToPHPUnitRector\PhpSpecToPHPUnitRectorTest
 */
final class PhpSpecClassToPHPUnitClassRector extends AbstractRector
{
    public function __construct(
        private readonly LetManipulator $letManipulator,
        private readonly PhpSpecRenaming $phpSpecRenaming,
        private readonly PhpSpecBehaviorNodeDetector $phpSpecBehaviorNodeDetector,
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

        $propertyName = $this->phpSpecRenaming->resolveObjectPropertyName($node);

        // @todo here move? :)
        $this->phpSpecRenaming->renameClass($node);
        $this->phpSpecRenaming->renameExtends($node);

        $testedClass = $this->phpSpecRenaming->resolveTestedClass($node);

        $testedObjectType = new ObjectType($testedClass);

        // add property
        $property = $this->nodeFactory->createPrivatePropertyFromNameAndType($propertyName, $testedObjectType);
        $newStmts = [$property];

        $classMethod = $node->getMethod('let');

        // add let if missing
        if (! $classMethod instanceof ClassMethod) {
            if (! $this->letManipulator->isLetNeededInClass($node)) {
                return null;
            }

            $letClassMethod = $this->createLetClassMethod($propertyName, $testedObjectType);
            $newStmts[] = $letClassMethod;
        }

        $node->stmts = array_merge($newStmts, (array) $node->stmts);

        return $this->removeSelfTypeMethod($node, $testedObjectType);
    }

    public function getRuleDefinition(): RuleDefinition
    {
        return new RuleDefinition('wip', []);
    }

    private function createLetClassMethod(string $propertyName, ObjectType $testedObjectType): ClassMethod
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

    /**
     * This is already checked on construction of object
     */
    private function removeSelfTypeMethod(Class_ $class, ObjectType $testedObjectType): Class_
    {
        foreach ($class->stmts as $key => $classStmt) {
            if (! $classStmt instanceof ClassMethod) {
                continue;
            }

            $classMethodStmts = (array) $classStmt->stmts;
            if (count($classMethodStmts) !== 1) {
                continue;
            }

            $innerClassMethodStmt = $this->resolveFirstNonExpressionStmt($classMethodStmts);
            if (! $innerClassMethodStmt instanceof MethodCall) {
                continue;
            }

            if (! $this->isName($innerClassMethodStmt->name, 'shouldHaveType')) {
                continue;
            }

            if (! isset($innerClassMethodStmt->args[0])) {
                continue;
            }

            if (! $innerClassMethodStmt->args[0] instanceof Arg) {
                continue;
            }

            // not the tested type
            if (! $this->valueResolver->isValue(
                $innerClassMethodStmt->args[0]->value,
                $testedObjectType->getClassName()
            )) {
                continue;
            }

            // remove class method
            unset($class->stmts[$key]);
        }

        return $class;
    }

    /**
     * @param Stmt[] $stmts
     */
    private function resolveFirstNonExpressionStmt(array $stmts): ?Node
    {
        if (! isset($stmts[0])) {
            return null;
        }

        $firstStmt = $stmts[0];
        if ($firstStmt instanceof Expression) {
            return $firstStmt->expr;
        }

        return $firstStmt;
    }
}
