# 17 Rules Overview

## ConsecutiveMockExpectationRector

Merge consecutive mock expectations to single `->willReturnMap()` call

- class: [`Rector\PhpSpecToPHPUnit\Rector\ClassMethod\ConsecutiveMockExpectationRector`](../rules/Rector/ClassMethod/ConsecutiveMockExpectationRector.php)

```diff
 use PhpSpec\ObjectBehavior;

 class DuringMethodSpec extends ObjectBehavior
 {
     public function is_should(MockedType $mockedType)
     {
-        $mockedType->set('first_key', 100)->shouldBeCalled();
-        $mockedType->set('second_key', 200)->shouldBeCalled();
+        $mockedType->expects($this->exactly(2))->method('set')
+            ->willReturnMap([
+                ['first_key', 100],
+                ['second_key', 200],
+            ]);
     }
 }
```

<br>

## DuringMethodCallRector

Split `shouldThrow()` and `during()` method to expected exception and method call

- class: [`Rector\PhpSpecToPHPUnit\Rector\ClassMethod\DuringMethodCallRector`](../rules/Rector/ClassMethod/DuringMethodCallRector.php)

```diff
 use PhpSpec\ObjectBehavior;

 class DuringMethodSpec extends ObjectBehavior
 {
     public function is_should()
     {
-        $this->shouldThrow(ValidationException::class)->during('someMethod');
+        $this->expectException(ValidationException::class);
+        $this->someMethod();
     }
 }
```

<br>

## ExpectedMockDeclarationRector

From PhpSpec mock expectations to PHPUnit mock expectations

- class: [`Rector\PhpSpecToPHPUnit\Rector\Expression\ExpectedMockDeclarationRector`](../rules/Rector/Expression/ExpectedMockDeclarationRector.php)

```diff
 use PhpSpec\ObjectBehavior;

 class ResultSpec extends ObjectBehavior
 {
     public function it_returns()
     {
-        $this->run()->shouldReturn(1000);
+        $this->expects($this->once())->method('run')->willReturn(1000);
     }
 }
```

<br>

## ImplicitLetInitializationRector

Add implicit object property to `setUp()` PHPUnit method

- class: [`Rector\PhpSpecToPHPUnit\Rector\Class_\ImplicitLetInitializationRector`](../rules/Rector/Class_/ImplicitLetInitializationRector.php)

```diff
 use PhpSpec\ObjectBehavior;

 final class SomeTypeSpec extends ObjectBehavior
 {
+    private SomeType $someType;
+
+    protected function setUp(): void
+    {
+        $this->someType = new SomeType();
+    }
+
     public function let()
     {
-        $this->run();
+        $this->someType->run();
     }
 }
```

<br>

## LetGoToTearDownClassMethodRector

Change `letGo()` method to `tearDown()` PHPUnit method

- class: [`Rector\PhpSpecToPHPUnit\Rector\Class_\LetGoToTearDownClassMethodRector`](../rules/Rector/Class_/LetGoToTearDownClassMethodRector.php)

```diff
 use PhpSpec\ObjectBehavior;

 final class LetGoLetMethods extends ObjectBehavior
 {
-    public function letGo()
+    protected function tearDown(): void
     {
     }
 }
```

<br>

## LetToSetUpClassMethodRector

Change `let()` method to `setUp()` PHPUnit method, including property mock initialization

- class: [`Rector\PhpSpecToPHPUnit\Rector\Class_\LetToSetUpClassMethodRector`](../rules/Rector/Class_/LetToSetUpClassMethodRector.php)

```diff
 use PhpSpec\ObjectBehavior;
+use PHPUnit\Framework\MockObject\MockObject;

 final class SomeTypeSpec extends ObjectBehavior
 {
-    public function let(SomeDependency $someDependency)
+    private SomeType $someType;
+
+    /**
+     * @var MockObject<SomeDependency>
+     */
+    private MockObject $someDependencyMock;
+
+    protected function setUp(): void
     {
-        $this->beConstructedWith($someDependency);
+        $this->someDependencyMock = $this->createMock(SomeDependency::class);
+        $this->someType = new SomeType($this->someDependencyMock);
     }
 }
```

<br>

## MoveParameterMockRector

Move parameter mocks to local mocks

- class: [`Rector\PhpSpecToPHPUnit\Rector\ClassMethod\MoveParameterMockRector`](../rules/Rector/ClassMethod/MoveParameterMockRector.php)

```diff
 use PhpSpec\ObjectBehavior;

 final class AddMockProperty extends ObjectBehavior
 {
-    public function it_should_handle_stuff(SomeType $someType)
+    public function it_should_handle_stuff()
     {
+        $someTypeMock = $this->createMock(SomeType::class);
     }
 }
```

<br>

## PhpSpecClassToPHPUnitClassRector

Rename spec class name and its parent class to PHPUnit format

- class: [`Rector\PhpSpecToPHPUnit\Rector\Class_\PhpSpecClassToPHPUnitClassRector`](../rules/Rector/Class_/PhpSpecClassToPHPUnitClassRector.php)

```diff
-use PhpSpec\ObjectBehavior;
+use PHPUnit\Framework\TestCase;

-class DefaultClassWithSetupProperty extends ObjectBehavior
+class DefaultClassWithSetupPropertyTest extends TestCase
 {
 }
```

<br>

## PromisesToAssertsRector

Convert promises and object construction to new instances

- class: [`Rector\PhpSpecToPHPUnit\Rector\Class_\PromisesToAssertsRector`](../rules/Rector/Class_/PromisesToAssertsRector.php)

```diff
 use PhpSpec\ObjectBehavior;

 class TestClassMethod extends ObjectBehavior
 {
     public function it_shoud_do()
     {
-        $this->beConstructedWith(5);
+        $testClassMethod = new \Rector\PhpSpecToPHPUnit\TestClassMethod(5);
     }
 }
```

<br>

## RemoveShouldBeCalledRector

Remove `shouldBeCalled()` as implicit in PHPUnit, also empty `willReturn()` as no return is implicit in PHPUnit

- class: [`Rector\PhpSpecToPHPUnit\Rector\MethodCall\RemoveShouldBeCalledRector`](../rules/Rector/MethodCall/RemoveShouldBeCalledRector.php)

```diff
 use PhpSpec\ObjectBehavior;

 class ResultSpec extends ObjectBehavior
 {
     public function it_is_initializable()
     {
-        $this->run()->shouldBeCalled();
+        $this->run();

-        $this->go()->willReturn();
+        $this->go();
     }
 }
```

<br>

## RemoveShouldHaveTypeRector

Remove `shouldHaveType()` check as pointless in time of PHP 7.0 types

- class: [`Rector\PhpSpecToPHPUnit\Rector\ClassMethod\RemoveShouldHaveTypeRector`](../rules/Rector/ClassMethod/RemoveShouldHaveTypeRector.php)

```diff
 use PhpSpec\ObjectBehavior;

 class RenameMethodTest extends ObjectBehavior
 {
-    public function is_shoud_have_type()
-    {
-        $this->shouldHaveType(SomeType::class);
-    }
 }
```

<br>

## RenameSpecNamespacePrefixToTestRector

Rename spec\ to Tests\ namespace

- class: [`Rector\PhpSpecToPHPUnit\Rector\Namespace_\RenameSpecNamespacePrefixToTestRector`](../rules/Rector/Namespace_/RenameSpecNamespacePrefixToTestRector.php)

```diff
-namespace spec\SomeNamespace;
+namespace Tests\SomeNamespace;

 use PhpSpec\ObjectBehavior;

 class SomeTest extends ObjectBehavior
 {
 }
```

<br>

## RenameTestClassMethodRector

Rename test method from underscore PhpSpec syntax to test* PHPUnit syntax

- class: [`Rector\PhpSpecToPHPUnit\Rector\ClassMethod\RenameTestClassMethodRector`](../rules/Rector/ClassMethod/RenameTestClassMethodRector.php)

```diff
 use PhpSpec\ObjectBehavior;

 class RenameMethodTest extends ObjectBehavior
 {
-    public function is_shoud_be_valid()
+    public function testShouldBeValid(): void
     {
     }
 }
```

<br>

## ShouldNeverBeCalledRector

Handle `shouldNotBeCalled()` expectations

- class: [`Rector\PhpSpecToPHPUnit\Rector\Expression\ShouldNeverBeCalledRector`](../rules/Rector/Expression/ShouldNeverBeCalledRector.php)

```diff
 use PhpSpec\ObjectBehavior;

 class ResultSpec extends ObjectBehavior
 {
     public function it_is_initializable()
     {
-        $this->run()->shouldNotBeCalled();
+        $this->expects($this->never())->run();
     }
 }
```

<br>

## ShouldNotThrowRector

Handle `shouldNotThrow()` expectations

- class: [`Rector\PhpSpecToPHPUnit\Rector\Expression\ShouldNotThrowRector`](../rules/Rector/Expression/ShouldNotThrowRector.php)

```diff
 use PhpSpec\ObjectBehavior;

 class ResultSpec extends ObjectBehavior
 {
     public function it_is_initializable()
     {
-        $this->shouldNotThrow(Exception::class)->during(
-            'someMethodCall',
-            ['someArguments']
-        );
+        // should not throw an exception
+        $this->someMethodCall('someArguments');
     }
 }
```

<br>

## ShouldThrowAndInstantiationOrderRector

Reorder and rename `shouldThrow()` method to mark before instantiation

- class: [`Rector\PhpSpecToPHPUnit\Rector\ClassMethod\ShouldThrowAndInstantiationOrderRector`](../rules/Rector/ClassMethod/ShouldThrowAndInstantiationOrderRector.php)

```diff
 use PhpSpec\ObjectBehavior;

 class RenameMethodTest extends ObjectBehavior
 {
     public function is_should()
     {
+        $this->expectException(ValidationException::class);
         $this->beConstructedThrough('create', [$data]);
-        $this->shouldThrow(ValidationException::class)->duringInstantiation();
     }
 }
```

<br>

## WithArgumentsMethodCallRector

Migrate ->with(Arguments::*()) call to PHPUnit

- class: [`Rector\PhpSpecToPHPUnit\Rector\MethodCall\WithArgumentsMethodCallRector`](../rules/Rector/MethodCall/WithArgumentsMethodCallRector.php)

```diff
 use PhpSpec\ObjectBehavior;
-use Prophecy\Argument;

 class ResultSpec extends ObjectBehavior
 {
     public function it_is_initializable()
     {
-        $this->run()->with(Arguments::cetera());
+        $this->run()->with($this->any());
     }
 }
```

<br>
