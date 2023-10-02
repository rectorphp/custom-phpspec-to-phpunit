<?php

declare(strict_types=1);

namespace Rector\PhpSpecToPHPUnit\Rector\ClassMethod;

use PhpParser\Node;
use PhpParser\Node\Identifier;
use PhpParser\Node\Stmt\ClassMethod;
use Rector\Core\Rector\AbstractRector;
use Rector\PhpSpecToPHPUnit\Naming\PhpSpecRenaming;
use Rector\PhpSpecToPHPUnit\NodeAnalyzer\PhpSpecBehaviorNodeDetector;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;

/**
 * @see \Rector\PhpSpecToPHPUnit\Tests\Rector\ClassMethod\RenameTestMethodRector\RenameTestMethodRectorTest
 */
final class RenameTestMethodRector extends AbstractRector
{
    public function __construct(
        private readonly PhpSpecRenaming $phpSpecRenaming,
        private readonly PhpSpecBehaviorNodeDetector $phpSpecBehaviorNodeDetector,
    ) {
    }

    /**
     * @return array<class-string<Node>>
     */
    public function getNodeTypes(): array
    {
        return [ClassMethod::class];
    }

    /**
     * @param ClassMethod $node
     */
    public function refactor(Node $node): ?Node
    {
        if (! $this->phpSpecBehaviorNodeDetector->isInPhpSpecBehavior($node)) {
            return null;
        }

        if (! $node->isPublic()) {
            return null;
        }

        // special case, @see https://johannespichler.com/writing-custom-phpspec-matchers/
        if ($this->isNames($node, ['getMatchers', 'let', 'letGo'])) {
            return null;
        }

        $methodName = $this->getName($node);

        // is already renamed
        if (str_starts_with($methodName, 'test')) {
            return null;
        }

        // change name to phpunit test case format
        $phpUnitTestMethodName = $this->phpSpecRenaming->resolvePHPUnitTestMethodName($methodName);
        $node->name = new Identifier($phpUnitTestMethodName);

        // @todo decouple
        //        // reorder instantiation + expected exception
        //        foreach ((array) $node->stmts as $key => $stmt) {
        //            $previousStmt = $node->stmts[$key - 1] ?? null;
        //
        //            // has duringInstantiation() method?
        //
        //            if (! $this->hasMethodCall($stmt, 'duringInstantiation')) {
        //                continue;
        //            }
        //
        //            if (! $previousStmt instanceof Stmt) {
        //                continue;
        //            }
        //
        //            if ($this->hasMethodCall($previousStmt, 'beConstructedThrough')) {
        //                $node->stmts[$key - 1] = $stmt;
        //                $node->stmts[$key] = $previousStmt;
        //            }
        //        }

        return $node;
    }

    public function getRuleDefinition(): RuleDefinition
    {
        return new RuleDefinition('Rename test method from underscore PhpSpec syntax to test* PHPUnit syntax', [
            new CodeSample(
                <<<'CODE_SAMPLE'
use PhpSpec\ObjectBehavior;

class RenameMethodTest extends ObjectBehavior
{
    public function is_shoud_be_valid()
    {
    }
}
CODE_SAMPLE
                ,
                <<<'CODE_SAMPLE'
use PhpSpec\ObjectBehavior;

class RenameMethodTest extends ObjectBehavior
{
    public function testShoudBeValid()
    {
    }
}
CODE_SAMPLE
            ),

        ]);
    }

    //    private function hasMethodCall(Stmt $stmt, string $methodName): bool
    //    {
    //        return (bool) $this->betterNodeFinder->findFirst($stmt, function (Node $node) use ($methodName): bool {
    //            if (! $node instanceof MethodCall) {
    //                return false;
    //            }
    //
    //            return $this->isName($node->name, $methodName);
    //        });
    //    }
}
