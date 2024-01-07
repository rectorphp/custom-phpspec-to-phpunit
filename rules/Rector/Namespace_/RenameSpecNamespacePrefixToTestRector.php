<?php

declare(strict_types=1);

namespace Rector\PhpSpecToPHPUnit\Rector\Namespace_;

use PhpParser\Node;
use PhpParser\Node\Name;
use PhpParser\Node\Stmt\Namespace_;
use Rector\PhpSpecToPHPUnit\StringUtils;
use Rector\Rector\AbstractRector;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;

/**
 * @see \Rector\PhpSpecToPHPUnit\Tests\Rector\Namespace_\RenameSpecNamespacePrefixToTestRector\RenameSpecNamespacePrefixToTestRectorTest
 */
final class RenameSpecNamespacePrefixToTestRector extends AbstractRector
{
    public function getRuleDefinition(): RuleDefinition
    {
        return new RuleDefinition('Rename spec\\ to Tests\\ namespace', [
            new CodeSample(
                <<<'CODE_SAMPLE'
namespace spec\SomeNamespace;

use PhpSpec\ObjectBehavior;

class SomeTest extends ObjectBehavior
{
}
CODE_SAMPLE
                ,
                <<<'CODE_SAMPLE'
namespace Tests\SomeNamespace;

use PhpSpec\ObjectBehavior;

class SomeTest extends ObjectBehavior
{
}
CODE_SAMPLE
            ),

        ]);
    }

    /**
     * @return array<class-string<Node>>
     */
    public function getNodeTypes(): array
    {
        return [Namespace_::class];
    }

    /**
     * @param Namespace_ $node
     */
    public function refactor(Node $node): ?Namespace_
    {
        $namespaceName = $this->getName($node);
        if ($namespaceName === null) {
            return null;
        }

        if (strncmp($namespaceName, 'spec\\', strlen('spec\\')) !== 0) {
            return null;
        }

        $newNamespaceName = StringUtils::removePrefixes($namespaceName, ['spec\\']);
        $node->name = new Name('Tests\\' . $newNamespaceName);

        return $node;
    }
}
