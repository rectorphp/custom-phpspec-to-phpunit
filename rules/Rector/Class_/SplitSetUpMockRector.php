<?php

declare(strict_types=1);

namespace Rector\PhpSpecToPHPUnit\Rector\Class_;

use PhpParser\Node;
use PhpParser\Node\Expr\MethodCall;
use PhpParser\Node\Expr\PropertyFetch;
use PhpParser\Node\Param;
use PhpParser\Node\Stmt\Class_;
use PhpParser\Node\Stmt\ClassMethod;
use PhpParser\Node\Stmt\Expression;
use Rector\PhpParser\Node\Value\ValueResolver;
use Rector\PhpSpecToPHPUnit\NodeFinder\MethodCallFinder;
use Rector\Rector\AbstractRector;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;

/**
 * @see \Rector\PhpSpecToPHPUnit\Tests\Rector\Class_\SplitSetUpMockRector\SplitSetUpMockRectorTest
 */
final class SplitSetUpMockRector extends AbstractRector
{
    public function __construct(
        private ValueResolver $valueResolver,
    ) {
    }

    /**
     * @return array<class-string<Node>>
     */
    public function getNodeTypes(): array
    {
        return [Class_::class];
    }

    public function getRuleDefinition(): RuleDefinition
    {
        return new RuleDefinition(
            'Move mock expectations from setUp() to particular methods where not overriden',
            [
                new CodeSample(
                    <<<'CODE_SAMPLE'
use PHPUnit\Framework\TestCase;

final class SomeTest extends TestCase
{
    private $someClassMock;

    protected function setUp()
    {
        $this->someClassMock = $this->createMock(SomeClass::class);
        $this->someClassMock->expect($this->once())->method('someMethod')->willReturn('someValue');
    }

    public function testSome()
    {
    }

    public function testAnother()
    {
        $this->someClassMock->expect($this->once())->method('someMethod')->willReturn('differentValue');
    }
}
CODE_SAMPLE
                    ,
                    <<<'CODE_SAMPLE'
use PHPUnit\Framework\TestCase;

final class SomeTest extends TestCase
{
    private $someClassMock;

    protected function setUp()
    {
        $this->someClassMock = $this->createMock(SomeClass::class);
    }

    public function testSome()
    {
        $this->someClassMock->expect($this->once())->method('someMethod')->willReturn('someValue');
    }

    public function testAnother()
    {
        $this->someClassMock->expect($this->once())->method('someMethod')->willReturn('differentValue');
    }
}
CODE_SAMPLE
                ),
            ]
        );
    }

    /**
     * @param Class_ $node
     */
    public function refactor(Node $node): ?Node
    {
        $setUpClassMethod = $node->getMethod('setUp');
        if (! $setUpClassMethod instanceof ClassMethod) {
            return null;
        }

        $setupMethodCallsStmtKeys = [];

        foreach ((array) $setUpClassMethod->stmts as $key => $classMethodStmt) {
            if (! $classMethodStmt instanceof Expression) {
                continue;
            }

            if (! $classMethodStmt->expr instanceof MethodCall) {
                continue;
            }

            $methodCall = $classMethodStmt->expr;

            // must be global property fetch, later overridden
            $topMostMethodCall = $methodCall;
            while ($topMostMethodCall->var instanceof MethodCall) {
                $topMostMethodCall = $topMostMethodCall->var;
            }

            if (! $topMostMethodCall->var instanceof PropertyFetch) {
                continue;
            }

            // must be expect() mock method call
            if (! MethodCallFinder::hasByName($methodCall, 'expect')) {
                continue;
            }

            $setupMethodCallsStmtKeys[$key] = $methodCall;
        }

        if ($setupMethodCallsStmtKeys === []) {
            return null;
        }

        $hasChanged = false;
        $setupMethodCallsStmtKeysToAddToTestMethods = [];

        foreach ($setupMethodCallsStmtKeys as $key => $mockMethodCall) {
            $mockedMethodName = $this->resolveMockedMethodName($mockMethodCall);
            if (! is_string($mockedMethodName)) {
                continue;
            }

            foreach ($node->getMethods() as $classMethod) {
                if (! $classMethod->isPublic()) {
                    continue;
                }

                $classMethodName = $this->getName($classMethod);

                // we look only for test methods
                if (! str_starts_with($classMethodName, 'test')) {
                    continue;
                }

                foreach ((array) $classMethod->stmts as $stmt) {
                    if (! $stmt instanceof Expression) {
                        continue;
                    }

                    if (! $stmt->expr instanceof MethodCall) {
                        continue;
                    }

                    $methodCall = $stmt->expr;

                    // must be global property fetch, later overridden
                    $topMostMethodCall = $methodCall;
                    while ($topMostMethodCall->var instanceof MethodCall) {
                        $topMostMethodCall = $topMostMethodCall->var;
                    }

                    if (! $topMostMethodCall->var instanceof PropertyFetch) {
                        continue;
                    }

                    // must be expect() mock method call
                    if (! MethodCallFinder::hasByName($methodCall, 'expect')) {
                        continue;
                    }

                    $methodMethodCall = MethodCallFinder::findByName($methodCall, 'method');
                    if (! $methodMethodCall instanceof MethodCall) {
                        continue;
                    }

                    $currenMockedMethodName = $this->valueResolver->getValue($methodMethodCall->getArgs()[0]->value);
                    if (! is_string($currenMockedMethodName)) {
                        continue;
                    }

                    if ($mockedMethodName === $currenMockedMethodName) {
                        // we have a match
                        // we need to move the method call from setUp() to the test method
                        unset($setUpClassMethod->stmts[$key]);

                        $setupMethodCallsStmtKeysToAddToTestMethods[] = $key;

                        $hasChanged = true;
                    }
                }
            }
        }

        foreach ($setupMethodCallsStmtKeysToAddToTestMethods as $setupMethodCallStmtKeyToAddToTestMethod) {
            $setUpMockMethodCall = $setupMethodCallsStmtKeys[$setupMethodCallStmtKeyToAddToTestMethod];
            $setUpMockMethodName = $this->resolveMockedMethodName($setUpMockMethodCall);
            if (! is_string($setUpMockMethodName)) {
                continue;
            }

            foreach ($node->getMethods() as $classMethod) {
                if (! $classMethod->isPublic()) {
                    continue;
                }

                $classMethodName = $this->getName($classMethod);

                // we look only for test methods
                if (! str_starts_with($classMethodName, 'test')) {
                    continue;
                }

                // already set, do not add
                if ($this->isMockingMethodName($classMethod, $setUpMockMethodName)) {
                    continue;
                }

                $classMethod->stmts = array_merge([new Expression($setUpMockMethodCall)], (array) $classMethod->stmts);
                $hasChanged = true;
            }
        }

        if ($hasChanged) {
            return $node;
        }

        return null;
    }

    private function resolveMockedMethodName(MethodCall $mockMethodCall): ?string
    {
        $methodMethodCall = MethodCallFinder::findByName($mockMethodCall, 'method');
        if (! $methodMethodCall instanceof MethodCall) {
            return null;
        }

        $firstArg = $methodMethodCall->getArgs()[0];

        return $this->valueResolver->getValue($firstArg->value);
    }

    private function isMockingMethodName(ClassMethod $classMethod, string $mockedMethodName): bool
    {
        $methodMethodCall = MethodCallFinder::findByName($classMethod, 'method');
        if (! $methodMethodCall instanceof MethodCall) {
            return false;
        }

        $firstArg = $methodMethodCall->getArgs()[0];
        return $this->valueResolver->isValue($firstArg->value, $mockedMethodName);
    }
}
