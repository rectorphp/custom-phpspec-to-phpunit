<?php

declare(strict_types=1);

namespace Rector\PhpSpecToPHPUnit\Rector\ClassMethod;

use PhpParser\Node;
use PhpParser\Node\Identifier;
use PhpParser\Node\Stmt\Class_;
use PhpParser\Node\Stmt\ClassMethod;
use Rector\Core\Rector\AbstractRector;
use Rector\Core\ValueObject\MethodName;
use Rector\PhpSpecToPHPUnit\NodeAnalyzer\PhpSpecBehaviorNodeDetector;
use Rector\Privatization\NodeManipulator\VisibilityManipulator;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;

/**
 * @see \Rector\PhpSpecToPHPUnit\Tests\Rector\Class_\SetUpTearDownClassMethodRector\SetUpTearDownClassMethodRectorTest
 */
final class SetUpTearDownClassMethodRector extends AbstractRector
{
    public function __construct(
        private readonly VisibilityManipulator $visibilityManipulator,
        private readonly PhpSpecBehaviorNodeDetector $phpSpecBehaviorNodeDetector,
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

        $hasChanged = false;

        $letClassMethod = $node->getMethod('let');
        if ($letClassMethod instanceof ClassMethod) {
            $this->renameToSetUpClassMethod($letClassMethod);

            $hasChanged = true;
        }

        $letGoClassMethod = $node->getMethod('letGo');
        if ($letGoClassMethod instanceof ClassMethod) {
            $this->renameLetGoToTearDown($letGoClassMethod);

            $hasChanged = true;
        }

        if (! $hasChanged) {
            return null;
        }

        return $node;
    }

    public function getRuleDefinition(): RuleDefinition
    {
        return new RuleDefinition('Change let() and letGo() methods to setUp() and tearDown()', [
            new CodeSample(
                <<<'CODE_SAMPLE'
use PhpSpec\ObjectBehavior;

final class LetGoLetMethods extends ObjectBehavior
{
    public function let()
    {
    }

    public function letGo()
    {
    }
}
CODE_SAMPLE
                ,
                <<<'CODE_SAMPLE'
use PhpSpec\ObjectBehavior;

final class LetGoLetMethods extends ObjectBehavior
{
    protected function setUp(): void
    {
    }

    protected function tearDown(): void
    {
    }
}
CODE_SAMPLE
            ),
        ]);
    }

    private function renameToSetUpClassMethod(ClassMethod $classMethod): ClassMethod
    {
        $classMethod->name = new Identifier(MethodName::SET_UP);
        $classMethod->returnType = new Identifier('void');
        $this->visibilityManipulator->makeProtected($classMethod);

        return $classMethod;
    }

    private function renameLetGoToTearDown(ClassMethod $classMethod): ClassMethod
    {
        $classMethod->name = new Identifier('tearDown');
        $classMethod->returnType = new Identifier('void');
        $this->visibilityManipulator->makeProtected($classMethod);

        return $classMethod;
    }
}
