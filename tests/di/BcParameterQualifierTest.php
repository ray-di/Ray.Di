<?php

declare(strict_types=1);

namespace Ray\Di;

use PHPUnit\Framework\TestCase;
use Ray\Di\Annotation\FakeInjectOne;
use Ray\Di\Annotation\FakeQualifierOnly;
use Ray\Di\Annotation\FakeQualifierWithValue;
use ReflectionMethod;

class BcParameterQualifierTest extends TestCase
{
    public function testGetNamesFromMethodLevelAttribute(): void
    {
        $method = new ReflectionMethod(FakeBcParameterQualifierClass::class, 'setSingleParam');
        /** @psalm-suppress DeprecatedClass */
        $names = BcParameterQualifier::getNames($method);

        $this->assertSame(['param' => FakeInjectOne::class], $names);
    }

    public function testNoNamesForMultipleParametersWithNonMatchingValue(): void
    {
        $method = new ReflectionMethod(FakeBcParameterQualifierClass::class, 'setMultipleParams');
        /** @psalm-suppress DeprecatedClass */
        $names = BcParameterQualifier::getNames($method);

        $this->assertSame([], $names);
    }

    public function testNamesForMultipleParametersWithMatchingValue(): void
    {
        $method = new ReflectionMethod(FakeBcParameterQualifierClass::class, 'setMultipleParamsWithMatchingValue');
        /** @psalm-suppress DeprecatedClass */
        $names = BcParameterQualifier::getNames($method);

        $this->assertSame(['param1' => FakeGearStickInject::class], $names);
    }

    public function testConstructorMultiParamWithMatchingValue(): void
    {
        $method = new ReflectionMethod(FakeBcConstructorMultiParamClass::class, '__construct');
        /** @psalm-suppress DeprecatedClass */
        $names = BcParameterQualifier::getNames($method);

        $this->assertSame(['param2' => FakeQualifierWithValue::class], $names);
    }

    public function testNoNamesWhenParameterHasQualifier(): void
    {
        $method = new ReflectionMethod(FakeBcParameterQualifierClass::class, 'setSingleParamWithQualifier');
        /** @psalm-suppress DeprecatedClass */
        $names = BcParameterQualifier::getNames($method);

        $this->assertSame([], $names);
    }

    public function testNoNamesForNonQualifierAttribute(): void
    {
        $method = new ReflectionMethod(FakeBcParameterQualifierClass::class, 'setSingleParamWithInjectOnly');
        /** @psalm-suppress DeprecatedClass */
        $names = BcParameterQualifier::getNames($method);

        $this->assertSame([], $names);
    }

    public function testNoNamesForMethodWithoutInjectInterface(): void
    {
        $method = new ReflectionMethod(FakeBcParameterQualifierClass::class, 'setSingleParamNoInject');
        /** @psalm-suppress DeprecatedClass */
        $names = BcParameterQualifier::getNames($method);

        $this->assertSame([], $names);
    }

    public function testNamesForTargetMethodOnly(): void
    {
        // FakeGearStickInject has TARGET_METHOD only
        // BC parameter qualifier now supports TARGET_METHOD-only attributes for backward compatibility
        $method = new ReflectionMethod(FakeBcParameterQualifierClass::class, 'setSingleParamMethodOnly');
        /** @psalm-suppress DeprecatedClass */
        $names = BcParameterQualifier::getNames($method);

        $this->assertSame(['param' => FakeGearStickInject::class], $names);
    }

    public function testConstructorWithQualifierOnly(): void
    {
        // Constructor with Qualifier-only attribute (no InjectInterface)
        // BC parameter qualifier should apply for constructors (InjectInterface is implicit)
        $method = new ReflectionMethod(FakeBcConstructorQualifierClass::class, '__construct');
        /** @psalm-suppress DeprecatedClass */
        $names = BcParameterQualifier::getNames($method);

        $this->assertSame(['param' => FakeQualifierOnly::class], $names);
    }
}
