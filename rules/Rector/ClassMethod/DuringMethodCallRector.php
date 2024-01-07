<?php

declare(strict_types=1);

namespace Rector\PhpSpecToPHPUnit\Rector\ClassMethod;

use PhpParser\Node\Stmt\Class_;
use PhpParser\Node;
use Rector\PhpSpecToPHPUnit\Enum\PhpSpecMethodName;
use Rector\PhpSpecToPHPUnit\Naming\PhpSpecRenaming;
use Rector\PhpSpecToPHPUnit\NodeAnalyzer\DuringAndRelatedMethodCallMatcher;
use Rector\PhpSpecToPHPUnit\NodeFactory\ExpectExceptionMethodCallFactory;
use Rector\PhpSpecToPHPUnit\ValueObject\DuringAndRelatedMethodCall;
use Rector\Rector\AbstractRector;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;

/**
 * @see \Rector\PhpSpecToPHPUnit\Tests\Rector\ClassMethod\ShouldThrowAndInstantiationOrderRector\ShouldThrowAndInstantiationOrderRectorTest
 */
final class DuringMethodCallRector extends AbstractRector
{
    /**
     * @readonly
     * @var \Rector\PhpSpecToPHPUnit\NodeAnalyzer\DuringAndRelatedMethodCallMatcher
     */
    private $duringAndRelatedMethodCallMatcher;
    /**
     * @readonly
     * @var \Rector\PhpSpecToPHPUnit\NodeFactory\ExpectExceptionMethodCallFactory
     */
    private $expectExceptionMethodCallFactory;
    /**
     * @readonly
     * @var \Rector\PhpSpecToPHPUnit\Naming\PhpSpecRenaming
     */
    private $phpSpecRenaming;
    public function __construct(DuringAndRelatedMethodCallMatcher $duringAndRelatedMethodCallMatcher, ExpectExceptionMethodCallFactory $expectExceptionMethodCallFactory, PhpSpecRenaming $phpSpecRenaming)
    {
        $this->duringAndRelatedMethodCallMatcher = $duringAndRelatedMethodCallMatcher;
        $this->expectExceptionMethodCallFactory = $expectExceptionMethodCallFactory;
        $this->phpSpecRenaming = $phpSpecRenaming;
    }
    /**
     * @return array<class-string<Node>>
     */
    public function getNodeTypes(): array
    {
        return [Class_::class];
    }

    /**
     * @param Node\Stmt\Class_ $node
     */
    public function refactor(Node $node): ?Node
    {
        $testedObject = $this->phpSpecRenaming->resolveTestedObject($node);

        $hasChanged = false;

        foreach ($node->getMethods() as $classMethod) {
            if (! $classMethod->isPublic() || $classMethod->stmts === null) {
                continue;
            }

            // special case, @see https://johannespichler.com/writing-custom-phpspec-matchers/
            if ($this->isNames($node, PhpSpecMethodName::RESERVED_CLASS_METHOD_NAMES)) {
                continue;
            }

            foreach ($classMethod->stmts as $key => $stmt) {
                $duringAndRelatedMethodCall = $this->duringAndRelatedMethodCallMatcher->match(
                    $stmt,
                    PhpSpecMethodName::DURING
                );

                if (! $duringAndRelatedMethodCall instanceof DuringAndRelatedMethodCall) {
                    continue;
                }

                $newStmts = $this->expectExceptionMethodCallFactory->createExpectExceptionStmts(
                    $duringAndRelatedMethodCall
                );
                $newStmts[] = $this->expectExceptionMethodCallFactory->createMethodCallStmt(
                    $duringAndRelatedMethodCall,
                    $testedObject
                );

                array_splice($classMethod->stmts, $key, 1, $newStmts);

                $hasChanged = true;
            }
        }

        if ($hasChanged) {
            return $node;
        }

        return null;
    }

    public function getRuleDefinition(): RuleDefinition
    {
        return new RuleDefinition('Split shouldThrow() and during() method to expected exception and method call', [
            new CodeSample(
                <<<'CODE_SAMPLE'
use PhpSpec\ObjectBehavior;

class DuringMethodSpec extends ObjectBehavior
{
    public function is_should()
    {
        $this->shouldThrow(ValidationException::class)->during('someMethod');
    }
}
CODE_SAMPLE
                ,
                <<<'CODE_SAMPLE'
use PhpSpec\ObjectBehavior;

class DuringMethodSpec extends ObjectBehavior
{
    public function is_should()
    {
        $this->expectException(ValidationException::class);
        $this->someMethod();
    }
}
CODE_SAMPLE
            ),
        ]);
    }
}
