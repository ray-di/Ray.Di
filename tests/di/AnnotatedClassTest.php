<?php

declare(strict_types=1);

namespace Ray\Di;

use LogicException;
use PHPUnit\Framework\TestCase;
use Ray\Aop\ReflectionClass;

class AnnotatedClassTest extends TestCase
{
    /** @var AnnotatedClass */
    protected $annotatedClass;

    protected function setUp(): void
    {
        parent::setUp();
        $this->annotatedClass = new AnnotatedClass();
    }

    public function testInvoke(): void
    {
        $newInstance = $this->annotatedClass->getNewInstance(new ReflectionClass(FakeCar::class));
        $this->assertInstanceOf(NewInstance::class, $newInstance);
        $container = new Container();
        (new Bind($container, FakeTyreInterface::class))->to(FakeTyre::class);
        (new Bind($container, FakeEngineInterface::class))->to(FakeEngine::class);
        (new Bind($container, FakeHandleInterface::class))->toProvider(FakeHandleProvider::class);
        (new Bind($container, FakeMirrorInterface::class))->annotatedWith('right')->to(FakeMirrorRight::class);
        (new Bind($container, FakeMirrorInterface::class))->annotatedWith('left')->to(FakeMirrorRight::class);
        (new Bind($container, FakeGearStickInterface::class))->toProvider(FakeGearStickProvider::class);
        $car = $newInstance($container);
        if (! $car instanceof FakeCar) {
            throw new LogicException();
        }

        $this->assertInstanceOf(FakeCar::class, $car);
        $this->assertInstanceOf(FakeTyre::class, $car->frontTyre);
        $this->assertInstanceOf(FakeTyre::class, $car->rearTyre);
        $this->assertInstanceOf(FakeLeatherGearStick::class, $car->gearStick);
        $this->assertNull($car->hardtop);
    }

    /**
     * @phpstan-param class-string $class
     *
     * @dataProvider classProvider
     */
    public function testAnnotatedByAnnotation(string $class): void
    {
        $newInstance = $this->annotatedClass->getNewInstance(new ReflectionClass($class));
        $container = new Container();
        (new Bind($container, FakeMirrorInterface::class))->annotatedWith(FakeLeft::class)->to(FakeMirrorLeft::class);
        (new Bind($container, FakeMirrorInterface::class))->annotatedWith(FakeRight::class)->to(FakeMirrorRight::class);
        $handleBar = $newInstance($container);
        if (! $handleBar instanceof FakeHandleBar && ! $handleBar instanceof FakeHandleBarQualifier) {
            throw new LogicException();
        }

        $this->assertInstanceOf(FakeMirrorLeft::class, $handleBar->leftMirror);
        $this->assertInstanceOf(FakeMirrorRight::class, $handleBar->rightMirror);
    }

    /**
     * @return array<array<class-string>>
     */
    public function classProvider(): array
    {
        return [
            [FakeHandleBar::class],
            [FakeHandleBarQualifier::class],
        ];
    }
}
