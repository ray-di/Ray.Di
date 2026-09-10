<?php

declare(strict_types=1);

namespace Ray\Di;

use PHPUnit\Framework\TestCase;
use ReflectionParameter;

use function assert;
use function serialize;
use function unserialize;

class InjectionPointTest extends TestCase
{
    /** @var InjectionPointInterface */
    private $ip;

    /** @var ReflectionParameter */
    private $parameter;

    protected function setUp(): void
    {
        $this->parameter = new ReflectionParameter([FakeWalkRobot::class, '__construct'], 'rightLeg');
        $this->ip = new InjectionPoint($this->parameter);
    }

    public function testGetParameter(): void
    {
        $actual = $this->ip->getParameter();
        $this->assertSame($this->parameter, $actual);
    }

    public function testGetMethod(): void
    {
        $actual = $this->ip->getMethod();
        $this->assertSame((string) $this->parameter->getDeclaringFunction(), (string) $actual);
    }

    public function testGetClass(): void
    {
        $actual = $this->ip->getClass();
        $this->assertSame((string) $this->parameter->getDeclaringClass(), (string) $actual);
    }

    public function testGetQualifiers(): void
    {
        $annotations = $this->ip->getQualifiers();
        $this->assertCount(1, $annotations);
        $this->assertInstanceOf(FakeConstant::class, $annotations[0]);
    }

    /**
     * An InjectionPoint is serialized into the compiled container. After
     * unserialize() the ReflectionParameter is dropped and rebuilt lazily on
     * first access; getParameter()/getMethod()/getClass() must still return
     * a working ReflectionParameter/ReflectionMethod/ReflectionClass.
     */
    public function testSerializeRoundTripRestoresParameter(): void
    {
        /** @var InjectionPoint $restored */
        $restored = unserialize(serialize($this->ip));

        $this->assertInstanceOf(ReflectionParameter::class, $restored->getParameter());
        $this->assertSame('rightLeg', $restored->getParameter()->name);
        $this->assertSame((string) $this->parameter->getDeclaringFunction(), (string) $restored->getMethod());
        $this->assertSame((string) $this->parameter->getDeclaringClass(), (string) $restored->getClass());
    }

    /**
     * Unlike testSerializeRoundTripRestoresParameter(), this never calls
     * getParameter() first, so it exercises getMethod()'s own lazy-rebuild
     * path rather than a cache getParameter() already populated.
     */
    public function testGetMethodAfterUnserializeWithoutPriorGetParameter(): void
    {
        $restored = unserialize(serialize($this->ip));
        assert($restored instanceof InjectionPoint);

        $this->assertSame((string) $this->parameter->getDeclaringFunction(), (string) $restored->getMethod());
    }

    /** @see self::testGetMethodAfterUnserializeWithoutPriorGetParameter() for why this is separate from the combined round trip */
    public function testGetClassAfterUnserializeWithoutPriorGetParameter(): void
    {
        $restored = unserialize(serialize($this->ip));
        assert($restored instanceof InjectionPoint);

        $this->assertSame((string) $this->parameter->getDeclaringClass(), (string) $restored->getClass());
    }

    /**
     * A restored InjectionPoint that is never touched must remain
     * re-serializable: __serialize() reads the retained (class, function,
     * name) tuple, not the still-null live ReflectionParameter, so
     * unserialize -> serialize -> unserialize works even when getParameter()
     * is never called in between.
     */
    public function testSerializeRoundTripSurvivesUnusedReserialize(): void
    {
        $blob = serialize($this->ip);
        $restored = unserialize($blob);
        assert($restored instanceof InjectionPoint);

        $reserialized = unserialize(serialize($restored));
        assert($reserialized instanceof InjectionPoint);

        $this->assertSame('rightLeg', $reserialized->getParameter()->name);
    }

    /**
     * getQualifiers() must return every qualifier annotation on the method, not
     * just the first one. With two qualifier attributes both must be returned.
     */
    public function testGetQualifiersReturnsAllQualifiers(): void
    {
        $parameter = new ReflectionParameter([FakeMultiQualifierConsumer::class, '__construct'], 'engine');
        $ip = new InjectionPoint($parameter);
        $qualifiers = $ip->getQualifiers();
        $this->assertCount(2, $qualifiers);
        $classes = [$qualifiers[0]::class, $qualifiers[1]::class];
        $this->assertContains(FakeLeft::class, $classes);
        $this->assertContains(FakeRight::class, $classes);
    }
}
