<?php

namespace Ray\Di;

use Ray\Di\Exception\NotFound;
use Ray\Di\Exception\InvalidBind;
use Ray\Di\FakeEngine;

class BindTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var Bind
     */
    private $bind;

    protected function setUp()
    {
        parent::setUp();
        $this->bind = new Bind(new Container, FakeTyreInterface::class);
    }
    public function testGetBound()
    {
        $this->bind->to(FakeTyre::class);
        $bound = $this->bind->getBound();
        $this->assertInstanceOf(Dependency::class, $bound);
    }

    public function testToString()
    {
        $this->assertSame('Ray\Di\FakeTyreInterface-*', (string) $this->bind);
    }

    public function testInvalidToTest()
    {
        $this->setExpectedException(Notfound::class);
        $this->bind->to('invalid-class');
    }

    public function testInvalidToProviderTest()
    {
        $this->setExpectedException(Notfound::class);
        $this->bind->toProvider('invalid-class');
    }

    public function testInValidInterfaceBinding()
    {
        $this->setExpectedException(NotFound::class);
        new Bind(new Container, 'invalid-interface');
    }

    public function testUntargetedBind()
    {
        $bind = new Bind(new Container, FakeEngine::class);
        $dependency = $bind->getBound();
        $this->assertInstanceOf(Dependency::class, $dependency);
    }

    public function testUntargetedBindSingleton()
    {
        $bind = (new Bind(new Container, FakeEngine::class))->in(Scope::SINGLETON);
        $container = new Container;
        $dependency1 = $bind->getBound()->inject($container);
        $dependency2 = $bind->getBound()->inject($container);
        $this->assertSame(spl_object_hash($dependency1), spl_object_hash($dependency2));
    }

    public function testToConstructor()
    {
        $container = new Container;
        $container->add((new Bind($container, ''))->annotatedWith('tmp_dir')->toInstance('/tmp'));
        $container->add((new Bind($container, FakeLegInterface::class))->annotatedWith('left')->to(FakeLeftLeg::class));
        $container->add((new Bind($container, FakeRobotInterface::class))->toConstructor(FakeToConstructorRobot::class, 'tmpDir=tmp_dir,leg=left'));
        $instance = $container->getInstance(FakeRobotInterface::class, Name::ANY);
        /** @var $instance FakeToConstructorRobot */
        $this->assertInstanceOf(FakeLeftLeg::class, $instance->leg);
        $this->assertSame('/tmp', $instance->tmpDir);
    }

    public function testToConstructorWithMethodInjection()
    {
        $container = new Container;
        $container->add((new Bind($container, ''))->annotatedWith('tmp_dir')->toInstance('/tmp'));
        $container->add((new Bind($container, FakeLegInterface::class))->annotatedWith('left')->to(FakeLeftLeg::class));
        $container->add((new Bind($container, FakeEngineInterface::class))->to(FakeEngine::class));
        $container->add(
            (new Bind($container, FakeRobotInterface::class))->toConstructor(
                FakeToConstructorRobot::class,
                'tmpDir=tmp_dir,leg=left',
                (new InjectionPoints)->addMethod('setEngine')
            )
        );
        $instance = $container->getInstance(FakeRobotInterface::class, Name::ANY);
        /** @var $instance FakeToConstructorRobot */
        $this->assertInstanceOf(FakeEngine::class, $instance->engine);
    }
}
