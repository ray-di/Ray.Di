<?php

declare(strict_types=1);

namespace Ray\Compiler;

use PHPUnit\Framework\TestCase;
use Ray\Di\AbstractModule;
use Ray\Di\Injector;

class InjectorFactoryTest extends TestCase
{
    public function getInstanceRayDiInjector() : void
    {
        $injector = InjectorFactory::getInstance(
            function () : AbstractModule {
                return new FakeToBindPrototypeModule;
            },
            __DIR__ . '/tmp/base'
        );
        $instance = $injector->getInstance(FakeRobotInterface::class);
        $this->assertInstanceOf(FakeRobot::class, $instance);
        $this->assertInstanceOf(Injector::class, $injector);
    }

    public function getInstanceScriptInjector() : void
    {
        $injector = InjectorFactory::getInstance(
            function () : AbstractModule {
                $modue = new FakeToBindPrototypeModule;
                $modue->install(new FakeProdModule);

                return $modue;
            },
            __DIR__ . '/tmp/base'
        );
        $instance = $injector->getInstance(FakeRobotInterface::class);
        $this->assertInstanceOf(FakeRobot::class, $instance);
        $this->assertInstanceOf(ScriptInjector::class, $injector);
    }

    public function testInjectComplexModule() : void
    {
        $injector = InjectorFactory::getInstance(
            function () : AbstractModule {
                return new FakeCarModule;
            },
            __DIR__ . '/tmp/car'
        );
        $instance = $injector->getInstance(FakeCarInterface::class);
        $this->assertInstanceOf(FakeCar::class, $instance);
    }

    public function testInjectionPoint() : void
    {
        $injector = InjectorFactory::getInstance(
            function () : AbstractModule {
                return new FakeLoggerModule;
            },
            __DIR__ . '/tmp/logger'
        );
        $instance = $injector->getInstance(FakeLoggerConsumer::class);
        $this->assertInstanceOf(FakeLoggerConsumer::class, $instance);
    }
}
