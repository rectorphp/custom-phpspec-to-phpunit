<?php

declare(strict_types=1);

use Rector\Config\RectorConfig;
use Rector\PhpSpecToPHPUnit\Rector\Class_\LetGoToTearDownClassMethodRector;
use Rector\PhpSpecToPHPUnit\Rector\Class_\LetToSetUpClassMethodRector;
use Rector\PhpSpecToPHPUnit\Rector\Class_\PhpSpecClassToPHPUnitClassRector;
use Rector\PhpSpecToPHPUnit\Rector\Class_\PromisesToAssertsRector;
use Rector\PhpSpecToPHPUnit\Rector\ClassMethod\ConsecutiveMockExpectationRector;
use Rector\PhpSpecToPHPUnit\Rector\ClassMethod\DuringMethodCallRector;
use Rector\PhpSpecToPHPUnit\Rector\ClassMethod\MoveParameterMockRector;
use Rector\PhpSpecToPHPUnit\Rector\ClassMethod\RemoveShouldHaveTypeRector;
use Rector\PhpSpecToPHPUnit\Rector\ClassMethod\RenameTestClassMethodRector;
use Rector\PhpSpecToPHPUnit\Rector\ClassMethod\ShouldThrowAndInstantiationOrderRector;
use Rector\PhpSpecToPHPUnit\Rector\Expression\ExpectedMockDeclarationRector;
use Rector\PhpSpecToPHPUnit\Rector\Expression\ShouldNeverBeCalledRector;
use Rector\PhpSpecToPHPUnit\Rector\Expression\ShouldNotThrowRector;
use Rector\PhpSpecToPHPUnit\Rector\MethodCall\RemoveShouldBeCalledRector;
use Rector\PhpSpecToPHPUnit\Rector\MethodCall\WithArgumentsMethodCallRector;
use Rector\PhpSpecToPHPUnit\Rector\Namespace_\RenameSpecNamespacePrefixToTestRector;

/**
 * @see https://gnugat.github.io/2015/09/23/phpunit-with-phpspec.html
 * @see http://www.phpspec.net/en/stable/cookbook/construction.html
 */
return static function (RectorConfig $rectorConfig): void {
    $rectorConfig->rules([
        // namespace spec\
        RenameSpecNamespacePrefixToTestRector::class,

        // $this->shouldHaveType()
        RemoveShouldHaveTypeRector::class,
        DuringMethodCallRector::class,

        ShouldThrowAndInstantiationOrderRector::class,

        // convert into ->willReturnMap()
        ConsecutiveMockExpectationRector::class,
        PromisesToAssertsRector::class,

        ExpectedMockDeclarationRector::class,

        // ->shouldNeveBeCalled()
        ShouldNeverBeCalledRector::class,

        // should not throw
        ShouldNotThrowRector::class,

        // ->shouldBeCalled()
        RemoveShouldBeCalledRector::class,

        // public method let() {}
        LetToSetUpClassMethodRector::class,

        // public method letGo() {}
        LetGoToTearDownClassMethodRector::class,

        // ->with(Argument::cetera()) calls
        WithArgumentsMethodCallRector::class,

        MoveParameterMockRector::class,

        RenameTestClassMethodRector::class,

        PhpSpecClassToPHPUnitClassRector::class,
    ]);
};
