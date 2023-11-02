<?php

declare(strict_types=1);

use Rector\Config\RectorConfig;
use Rector\PhpSpecToPHPUnit\Rector\Class_\LetGoToTearDownClassMethodRector;
use Rector\PhpSpecToPHPUnit\Rector\Class_\LetToSetUpClassMethodRector;
use Rector\PhpSpecToPHPUnit\Rector\Class_\PhpSpecClassToPHPUnitClassRector;
use Rector\PhpSpecToPHPUnit\Rector\Class_\PromisesToAssertsRector;
use Rector\PhpSpecToPHPUnit\Rector\ClassMethod\DuringMethodCallRector;
use Rector\PhpSpecToPHPUnit\Rector\ClassMethod\MoveParameterMockRector;
use Rector\PhpSpecToPHPUnit\Rector\ClassMethod\RemoveShouldHaveTypeRector;
use Rector\PhpSpecToPHPUnit\Rector\ClassMethod\RenameTestClassMethodRector;
use Rector\PhpSpecToPHPUnit\Rector\ClassMethod\ShouldThrowAndInstantiationOrderRector;
use Rector\PhpSpecToPHPUnit\Rector\Expression\ExpectedMockDeclarationRector;
use Rector\PhpSpecToPHPUnit\Rector\Expression\ShouldNeverBeCalledRector;
use Rector\PhpSpecToPHPUnit\Rector\MethodCall\RemoveShouldBeCalledRector;
use Rector\PhpSpecToPHPUnit\Rector\Namespace_\RenameSpecNamespacePrefixToTestRector;
use Rector\PhpSpecToPHPUnit\Rector\Variable\MockVariableToPropertyFetchRector;

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

        PromisesToAssertsRector::class,

        ExpectedMockDeclarationRector::class,

        // ->shouldNeveBeCalled()
        ShouldNeverBeCalledRector::class,

        // ->shouldBeCalled()
        RemoveShouldBeCalledRector::class,

        // public method let() {}
        LetToSetUpClassMethodRector::class,

        // public method letGo() {}
        LetGoToTearDownClassMethodRector::class,

        RenameTestClassMethodRector::class,

        // @todo possibly not needed - only those in let()
        MoveParameterMockRector::class,

        MockVariableToPropertyFetchRector::class,

        PhpSpecClassToPHPUnitClassRector::class,
    ]);
};
