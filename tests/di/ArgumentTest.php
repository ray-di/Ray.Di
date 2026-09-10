<?php

declare(strict_types=1);

namespace Ray\Di;

use PHPUnit\Framework\TestCase;
use Ray\Aop\Bind;
use ReflectionMethod;
use ReflectionParameter;

use function assert;
use function serialize;
use function unserialize;

class ArgumentTest extends TestCase
{
    /** @var Argument */
    protected $argument;

    protected function setUp(): void
    {
        $this->argument = new Argument(new ReflectionParameter([FakeCar::class, '__construct'], 'engine'), Name::ANY);
    }

    public function testToString(): void
    {
        $this->assertSame('Ray\Di\FakeEngineInterface-' . Name::ANY, (string) $this->argument);
    }

    public function testToStringScalar(): void
    {
        $argument = new Argument(new ReflectionParameter([FakeInternalTypes::class, 'stringId'], 'id'), Name::ANY);
        $this->assertSame('-' . Name::ANY, (string) $argument);
    }

    public function testSerializable(): void
    {
        $argument = unserialize(serialize(new Argument(new ReflectionParameter([FakeInternalTypes::class, 'stringId'], 'id'), Name::ANY)));
        assert($argument instanceof Argument);
        $class = $argument->get()->getDeclaringFunction();
        $this->assertInstanceOf(ReflectionMethod::class, $class);
    }

    /**
     * A restored Argument that is never touched must remain re-serializable:
     * __serialize() reads the retained (class, method, param) tuple, not the
     * still-null live ReflectionParameter, so unserialize -> serialize ->
     * unserialize works even when get() is never called in between.
     */
    public function testSerializeRoundTripSurvivesUnusedReserialize(): void
    {
        $blob = serialize(new Argument(new ReflectionParameter([FakeInternalTypes::class, 'stringId'], 'id'), Name::ANY));
        $restored = unserialize($blob);
        assert($restored instanceof Argument);

        $reserialized = unserialize(serialize($restored));
        assert($reserialized instanceof Argument);

        $this->assertSame('id', $reserialized->get()->getName());
    }

    /**
     * accept() must still hand the visitor a working ReflectionParameter
     * after unserialize(), i.e. it must go through get() rather than the
     * raw (possibly still-null) $reflection property.
     */
    public function testAcceptAfterUnserializeSuppliesRebuiltParameter(): void
    {
        $blob = serialize(new Argument(new ReflectionParameter([FakeInternalTypes::class, 'stringId'], 'id'), Name::ANY));
        $restored = unserialize($blob);
        assert($restored instanceof Argument);

        $visitor = new class implements VisitorInterface
        {
            public ?ReflectionParameter $parameter = null;

            public function visitDependency(NewInstance $newInstance, ?string $postConstruct, bool $isSingleton)
            {
            }

            public function visitProvider(Dependency $dependency, string $context, bool $isSingleton)
            {
            }

            /** @param mixed $value */
            public function visitInstance($value)
            {
            }

            public function visitAspectBind(Bind $aopBind)
            {
            }

            public function visitNewInstance(string $class, SetterMethods $setterMethods, ?Arguments $arguments, ?AspectBind $bind)
            {
            }

            /** @inheritDoc */
            public function visitSetterMethods(array $setterMethods)
            {
            }

            public function visitSetterMethod(string $method, Arguments $arguments)
            {
            }

            /** @inheritDoc */
            public function visitArguments(array $arguments)
            {
            }

            /** @param mixed $defaultValue */
            public function visitArgument(string $index, bool $isDefaultAvailable, $defaultValue, ReflectionParameter $parameter)
            {
                $this->parameter = $parameter;
            }
        };

        $restored->accept($visitor);

        $this->assertInstanceOf(ReflectionParameter::class, $visitor->parameter);
        $this->assertSame('id', $visitor->parameter->getName());
    }

    /**
     * A required (non-optional, no default) parameter has no default available.
     */
    public function testRequiredParameterHasNoDefault(): void
    {
        $argument = new Argument(new ReflectionParameter([FakeCar::class, '__construct'], 'engine'), Name::ANY);
        $this->assertFalse($argument->isDefaultAvailable());
    }

    /**
     * A parameter with an explicit default value exposes that default.
     */
    public function testParameterWithDefaultValue(): void
    {
        $argument = new Argument(new ReflectionParameter([FakeHandleProvider::class, '__construct'], 'logo'), Name::ANY);
        $this->assertTrue($argument->isDefaultAvailable());
        $this->assertSame('nardi', $argument->getDefaultValue());
    }

    /**
     * A variadic parameter is optional yet has no retrievable default value.
     *
     * This pins the `isDefaultValueAvailable() || isOptional()` contract: a
     * variadic is optional (so the default must be considered available) even
     * though isDefaultValueAvailable() is false. Replacing the OR with an AND
     * would wrongly report the default as unavailable.
     */
    public function testVariadicParameterIsDefaultAvailable(): void
    {
        $argument = new Argument(new ReflectionParameter([FakeVariadicConstructor::class, '__construct'], 'engines'), Name::ANY);
        $this->assertTrue($argument->isDefaultAvailable());
    }
}
