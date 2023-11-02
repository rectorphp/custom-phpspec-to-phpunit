<?php

declare(strict_types=1);

namespace Rector\PhpSpecToPHPUnit\Rector\Class_;

use PhpParser\Node;
use PhpParser\Node\Identifier;
use PhpParser\Node\Name\FullyQualified;
use PhpParser\Node\Stmt\Class_;
use Rector\Core\Rector\AbstractRector;
use Rector\PhpSpecToPHPUnit\Naming\PhpSpecRenaming;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;

/**
 * @see \Rector\PhpSpecToPHPUnit\Tests\Rector\Class_\PhpSpecClassToPHPUnitClassRector\PhpSpecClassToPHPUnitClassRectorTest
 */
final class PhpSpecClassToPHPUnitClassRector extends AbstractRector
{
    public function __construct(
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

    /**
     * @param Class_ $node
     */
    public function refactor(Node $node): ?Node
    {
        // skip already renamed
        /** @var string $className */
        $className = $node->name->toString();
        if (str_ends_with($className, 'Test')) {
            return null;
        }

        // rename class and parent class
        $phpunitTestClassName = $this->phpSpecRenaming->createPHPUnitTestClassName($node);
        $node->name = new Identifier($phpunitTestClassName);
        $node->extends = new FullyQualified('PHPUnit\Framework\TestCase');

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
