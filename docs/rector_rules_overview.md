# 12 Rules Overview

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

 final class SomeTypeSpec extends ObjectBehavior
 {
-    public function let()
+    private SomeType $someType;
+
+    protected function setUp(): void
     {
+        $this->someType = new SomeType();
     }
 }
```

<br>

## MockVariableToPropertyFetchRector

Change local mock call to a property fetch mock call

- class: [`Rector\PhpSpecToPHPUnit\Rector\Variable\MockVariableToPropertyFetchRector`](../rules/Rector/Variable/MockVariableToPropertyFetchRector.php)

```diff
-* $mock->call()
- * ↓
- * $this->mock->call()
+// to be done
```

<br>

## MoveParameterMockToPropertyMockRector

Move public class method parameter mocks to properties mocks

- class: [`Rector\PhpSpecToPHPUnit\Rector\Class_\MoveParameterMockToPropertyMockRector`](../rules/Rector/Class_/MoveParameterMockToPropertyMockRector.php)

```diff
 use PhpSpec\ObjectBehavior;

 final class AddMockProperty extends ObjectBehavior
 {
+    private \Rector\PhpSpecToPHPUnit\Tests\Rector\Class_\AddMockPropertiesRector\Source\SomeType|\PHPUnit\Framework\MockObject\MockObject $someType;
+
     public function let(SomeType $someType)
     {
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

## PhpSpecMocksToPHPUnitMocksRector

From PhpSpec mock expectations to PHPUnit mock expectations

- class: [`Rector\PhpSpecToPHPUnit\Rector\Class_\PhpSpecMocksToPHPUnitMocksRector`](../rules/Rector/Class_/PhpSpecMocksToPHPUnitMocksRector.php)

```diff
 use PhpSpec\ObjectBehavior;

 class ResultSpec extends ObjectBehavior
 {
     public function it_is_initializable()
     {
-        $this->run()->shouldBeCalled();
+        $this->expects($this->atLeastOnce())->method('run');
     }
 }
```

<br>

## PhpSpecPromisesToPHPUnitAssertRector

Convert promises and object construction into objects

- class: [`Rector\PhpSpecToPHPUnit\Rector\Class_\PhpSpecPromisesToPHPUnitAssertRector`](../rules/Rector/Class_/PhpSpecPromisesToPHPUnitAssertRector.php)

```diff
 use PhpSpec\ObjectBehavior;

 class TestClassMethod extends ObjectBehavior
 {
     public function let()
     {
-        $this->beConstructedWith(5);
+        $this->testClassMethod = new \Rector\PhpSpecToPHPUnit\TestClassMethod(5);
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

## RenameTestMethodRector

Rename test method from underscore PhpSpec syntax to test* PHPUnit syntax

- class: [`Rector\PhpSpecToPHPUnit\Rector\ClassMethod\RenameTestMethodRector`](../rules/Rector/ClassMethod/RenameTestMethodRector.php)

```diff
 use PhpSpec\ObjectBehavior;

 class RenameMethodTest extends ObjectBehavior
 {
-    public function is_shoud_be_valid()
+    public function testShoudBeValid()
     {
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
