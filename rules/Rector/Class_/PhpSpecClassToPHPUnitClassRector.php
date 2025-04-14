<?php

declare(strict_types=1);

namespace Rector\PhpSpecToPHPUnit\Rector\Class_;

use PhpParser\Node;
use PhpParser\Node\Identifier;
use PhpParser\Node\Name\FullyQualified;
use PhpParser\Node\Stmt\Class_;
use PHPUnit\Framework\TestCase;
use Rector\PhpSpecToPHPUnit\Naming\PhpSpecRenaming;
use Rector\Privatization\NodeManipulator\VisibilityManipulator;
use Rector\Rector\AbstractRector;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;

/**
 * @see \Rector\PhpSpecToPHPUnit\Tests\Rector\Class_\PhpSpecClassToPHPUnitClassRector\PhpSpecClassToPHPUnitClassRectorTest
 */
final class PhpSpecClassToPHPUnitClassRector extends AbstractRector
{
    /**
     * @readonly
     */
    private PhpSpecRenaming $phpSpecRenaming;
    /**
     * @readonly
     */
    private VisibilityManipulator $visibilityManipulator;
    public function __construct(PhpSpecRenaming $phpSpecRenaming, VisibilityManipulator $visibilityManipulator)
    {
        $this->phpSpecRenaming = $phpSpecRenaming;
        $this->visibilityManipulator = $visibilityManipulator;
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
        // skip already renamed
        /** @var string $className */
        $className = $node->name->toString();
        if (substr_compare($className, 'Test', -strlen('Test')) === 0) {
            return null;
        }

        // rename class and parent class
        $phpunitTestClassName = $this->phpSpecRenaming->createPHPUnitTestClassName($node);
        $node->name = new Identifier($phpunitTestClassName);
        $node->extends = new FullyQualified(TestCase::class);

        $this->visibilityManipulator->makeFinal($node);

        return $node;
    }

    public function getRuleDefinition(): RuleDefinition
    {
        return new RuleDefinition('Rename spec class name and its parent class to PHPUnit format', [
            new CodeSample(
                <<<'CODE_SAMPLE'
use PhpSpec\ObjectBehavior;

class DefaultClassWithSetupProperty extends ObjectBehavior
{
}
CODE_SAMPLE
                ,
                <<<'CODE_SAMPLE'
use PHPUnit\Framework\TestCase;

class DefaultClassWithSetupPropertyTest extends TestCase
{
}
CODE_SAMPLE
            ),
        ]);
    }
}
