<?php

declare(strict_types=1);

namespace Rector\PhpSpecToPHPUnit\Rector\Class_;

use PhpParser\Node;
use PhpParser\Node\Identifier;
use PhpParser\Node\Stmt\Class_;
use PhpParser\Node\Stmt\ClassMethod;
use Rector\Core\Rector\AbstractRector;
use Rector\PhpSpecToPHPUnit\Enum\PhpSpecMethodName;
use Rector\PhpSpecToPHPUnit\NodeAnalyzer\PhpSpecBehaviorNodeDetector;
use Rector\Privatization\NodeManipulator\VisibilityManipulator;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;

/**
 * @see \Rector\PhpSpecToPHPUnit\Tests\Rector\Class_\LetGoToTearDownClassMethodRector\LetGoToTearDownClassMethodRectorTest
 */
final class LetGoToTearDownClassMethodRector extends AbstractRector
{
    /**
     * @readonly
     * @var \Rector\Privatization\NodeManipulator\VisibilityManipulator
     */
    private $visibilityManipulator;
    /**
     * @readonly
     * @var \Rector\PhpSpecToPHPUnit\NodeAnalyzer\PhpSpecBehaviorNodeDetector
     */
    private $phpSpecBehaviorNodeDetector;
    public function __construct(VisibilityManipulator $visibilityManipulator, PhpSpecBehaviorNodeDetector $phpSpecBehaviorNodeDetector)
    {
        $this->visibilityManipulator = $visibilityManipulator;
        $this->phpSpecBehaviorNodeDetector = $phpSpecBehaviorNodeDetector;
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

        $letGoClassMethod = $node->getMethod(PhpSpecMethodName::LET_GO);
        if (! $letGoClassMethod instanceof ClassMethod) {
            return null;
        }

        $letGoClassMethod->name = new Identifier('tearDown');
        $letGoClassMethod->returnType = new Identifier('void');

        $this->visibilityManipulator->makeProtected($letGoClassMethod);

        return $node;
    }

    public function getRuleDefinition(): RuleDefinition
    {
        return new RuleDefinition('Change letGo() method to tearDown() PHPUnit method', [
            new CodeSample(
                <<<'CODE_SAMPLE'
use PhpSpec\ObjectBehavior;

final class LetGoLetMethods extends ObjectBehavior
{
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
    protected function tearDown(): void
    {
    }
}
CODE_SAMPLE
            ),
        ]);
    }
}
