<?php

namespace Ray\Di;

class ContainerTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var Container
     */
    private $container;
    private $engine;

    public function setUp()
    {
        parent::setUp();
        $this->container = new Container;
        $this->engine = new FakeEngine;
        $bind = (new Bind($this->container, FakeEngineInterface::class))->toInstance($this->engine);
    }

    public function testGetDependency()
    {
        $dependencyIndex = FakeEngineInterface::class . '-' . Name::ANY;
        $instance = $this->container->getDependency($dependencyIndex);
        $this->assertInstanceOf(FakeEngine::class, $instance);
        $this->assertSame($this->engine, $instance);
    }

    public function testClassGetDependency() {
        $bind = (new Bind($this->container, FakeEngine::class))->toInstance($this->engine);
        $dependencyIndex = FakeEngine::class . '-' . Name::ANY;
        $instance = $this->container->getDependency($dependencyIndex);
        $this->assertInstanceOf(FakeEngine::class, $instance);
        $this->assertSame($this->engine, $instance);
    }

    public function testProviderGetDependency() {
        $bind = (new Bind($this->container, FakeEngine::class))->toProvider(FakeEngineProvider::class);
        $dependencyIndex = FakeEngine::class . '-' . Name::ANY;
        $instance = $this->container->getDependency($dependencyIndex);
        $this->assertInstanceOf(FakeEngine::class, $instance);
    }

    public function testGetInstance()
    {
        $instance = $this->container->getInstance(FakeEngineInterface::class, Name::ANY);
        $this->assertInstanceOf(FakeEngine::class, $instance);
        $this->assertSame($this->engine, $instance);
    }

    public function testClassGetInstance() {
        $bind = (new Bind($this->container, FakeEngine::class))->toInstance($this->engine);
        $instance = $this->container->getInstance(FakeEngine::class, Name::ANY);
        $this->assertInstanceOf(FakeEngine::class, $instance);
        $this->assertSame($this->engine, $instance);
    }

    public function testProviderGetInstance() {
        $bind = (new Bind($this->container, FakeEngine::class))->toProvider(FakeEngineProvider::class);
        $instance = $this->container->getInstance(FakeEngine::class, Name::ANY);
        $this->assertInstanceOf(FakeEngine::class, $instance);
    }

    public function testGetContainer()
    {
        $array = $this->container->getContainer();
        $dependencyIndex = FakeEngineInterface::class . '-' . Name::ANY;
        $this->assertArrayHasKey($dependencyIndex, $array);
    }

    public function testClassGetContainer() {
        $bind = (new Bind($this->container, FakeEngine::class))->toInstance($this->engine);
        $array = $this->container->getContainer();
        $dependencyIndex = FakeEngine::class . '-' . Name::ANY;
        $this->assertArrayHasKey($dependencyIndex, $array);
    }

    public function testMerge()
    {
        $extraContainer = new Container;
        $bind = (new Bind($this->container, FakeRobotInterface::class))->to(FakeRobot::class);
        $this->container->add($bind);
        $this->container->merge($extraContainer);
        $array = $this->container->getContainer();
        $this->assertArrayHasKey(FakeEngineInterface::class . '-' . Name::ANY,  $array);
        $this->assertArrayHasKey(FakeRobotInterface::class . '-' . Name::ANY,  $array);
    }
}
