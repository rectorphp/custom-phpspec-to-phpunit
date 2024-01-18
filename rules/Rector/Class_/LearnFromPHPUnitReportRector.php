<?php

declare(strict_types=1);

namespace Rector\PhpSpecToPHPUnit\Rector\Class_;

use PhpParser\Node;
use PhpParser\Node\Expr\MethodCall;
use PhpParser\Node\Identifier;
use PhpParser\Node\Param;
use PhpParser\Node\Stmt;
use PhpParser\Node\Stmt\Class_;
use PhpParser\Node\Stmt\ClassMethod;
use Rector\Contract\Rector\ConfigurableRectorInterface;
use Rector\Exception\ShouldNotHappenException;
use Rector\PhpSpecToPHPUnit\Enum\PHPUnitMethodName;
use Rector\PhpSpecToPHPUnit\NodeFinder\MethodCallFinder;
use Rector\PhpSpecToPHPUnit\PHPUnit\PHPUnitResultAnalyzer;
use Rector\PhpSpecToPHPUnit\ValueObject\PHPUnit\MoreThanOnceTestError;
use Rector\PhpSpecToPHPUnit\ValueObject\PHPUnit\NoCallTestError;
use Rector\Rector\AbstractRector;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;
use Webmozart\Assert\Assert;

/**
 * @see \Rector\PhpSpecToPHPUnit\Tests\Rector\Class_\LearnFromPHPUnitReportRector\LearnFromPHPUnitReportRectorTest
 */
final class LearnFromPHPUnitReportRector extends AbstractRector implements ConfigurableRectorInterface
{
    /**
     * @var \Rector\PhpSpecToPHPUnit\PHPUnit\PHPUnitResultAnalyzer
     */
    private $phpUnitResultAnalyzer;
    /**
     * @var string|null
     */
    private $phpunitReportFilePath;

    public function __construct(PHPUnitResultAnalyzer $phpUnitResultAnalyzer)
    {
        $this->phpUnitResultAnalyzer = $phpUnitResultAnalyzer;
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
            'Update $this->once() and $this->atLeastOnce() based on a PHPUint error report file contents',
            [
                new CodeSample(
                    <<<'CODE_SAMPLE'
use PHPUnit\Framework\TestCase;

final class SomeTest extends TestCase
{
    public function testSomething()
    {
        $this->someMock->expect($this->once())->method('someMethod');
    }
}
CODE_SAMPLE
                    ,
                    <<<'CODE_SAMPLE'
use PHPUnit\Framework\TestCase;

final class SomeTest extends TestCase
{
    public function testSomething()
    {
        $this->someMock->expect($this->atLeastOnce())->method('someMethod');
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
        if ($this->phpunitReportFilePath === null) {
            throw new ShouldNotHappenException();
        }

        // 1. first
        $moreThanOnceTestErrors = $this->phpUnitResultAnalyzer->resolveMoreThanOnceTestErrors(
            $this->phpunitReportFilePath
        );

        foreach ($moreThanOnceTestErrors as $moreThanOnceTestError) {
            $testClassMethod = $this->matchTestClassMethod($node, $moreThanOnceTestError);
            if (! $testClassMethod instanceof ClassMethod) {
                continue;
            }

            foreach ((array) $testClassMethod->stmts as $key => $classMethodStmt) {
                if (! MethodCallFinder::hasMethodMockByName(
                    $classMethodStmt,
                    $moreThanOnceTestError->getMockedMethod()
                )) {
                    continue;
                }

                unset($testClassMethod->stmts[$key]);
            }
        }

        // 2. second
        $noCallsTestErrors = $this->phpUnitResultAnalyzer->resolveNoCallTestErrors($this->phpunitReportFilePath);

        foreach ($noCallsTestErrors as $noCallsTestError) {
            $testClassMethod = $this->matchTestClassMethod($node, $noCallsTestError);
            if (! $testClassMethod instanceof ClassMethod) {
                continue;
            }

            foreach ((array) $testClassMethod->stmts as $classMethodStmt) {
                if (! MethodCallFinder::hasMethodMockByName($classMethodStmt, $noCallsTestError->getMockedMethod())) {
                    continue;
                }

                // replace $this->once() with $this->atLeastOnce()
                $this->replaceThisOnceWithAtLeastOnce($classMethodStmt);
            }
        }

        return $node;
    }

    /**
     * @param string[] $configuration
     */
    public function configure(array $configuration): void
    {
        // set path to file report
        Assert::count($configuration, 1);
        $phpunitReportFilePath = $configuration[0];

        Assert::string($phpunitReportFilePath);
        Assert::fileExists($phpunitReportFilePath);

        $this->phpunitReportFilePath = $phpunitReportFilePath;
    }

    /**
     * @param \Rector\PhpSpecToPHPUnit\ValueObject\PHPUnit\NoCallTestError|\Rector\PhpSpecToPHPUnit\ValueObject\PHPUnit\MoreThanOnceTestError $noCallsTestError
     */
    private function matchTestClassMethod(
        Class_ $class,
        $noCallsTestError
    ): ?ClassMethod {
        // are we in the right test class?
        if (! $this->isName($class, $noCallsTestError->getTestClass())) {
            return null;
        }

        return $class->getMethod($noCallsTestError->getTestClassMethod());
    }

    private function replaceThisOnceWithAtLeastOnce(Stmt $stsmt): void
    {
        $this->traverseNodesWithCallable($stsmt, function (Node $node): ?Node {
            if (! $node instanceof MethodCall) {
                return null;
            }

            if (! $this->isName($node->name, PHPUnitMethodName::ONCE)) {
                return null;
            }

            $node->name = new Identifier(PHPUnitMethodName::AT_LEAST_ONCE);
            return $node;
        });
    }
}
