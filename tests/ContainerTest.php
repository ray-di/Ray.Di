<?php

declare(strict_types=1);

namespace Ray\Di;

use BadMethodCallException;
use PHPUnit\Framework\TestCase;
use Ray\Aop\Matcher;
use Ray\Aop\Pointcut;
use Ray\Di\Exception\Unbound;

class ContainerTest extends TestCase
{
    /**
     * @var Container
     */
    private $container;

    /**
     * @var FakeEngine
     */
    private $engine;

    protected function setUp() : void
    {
        parent::setUp();
        $this->container = new Container;
        $this->engine = new FakeEngine;
        (new Bind($this->container, FakeEngineInterface::class))->toInstance($this->engine);
    }

    public function testGetDependency()
    {
        $dependencyIndex = FakeEngineInterface::class . '-' . Name::ANY;
        $instance = $this->container->getDependency($dependencyIndex);
        $this->assertInstanceOf(FakeEngine::class, $instance);
        $this->assertSame($this->engine, $instance);
    }

    public function testClassGetDependency()
    {
        (new Bind($this->container, FakeEngine::class))->toInstance($this->engine);
        $dependencyIndex = FakeEngine::class . '-' . Name::ANY;
        $instance = $this->container->getDependency($dependencyIndex);
        $this->assertInstanceOf(FakeEngine::class, $instance);
        $this->assertSame($this->engine, $instance);
    }

    public function testProviderGetDependency()
    {
        (new Bind($this->container, FakeEngine::class))->toProvider(FakeEngineProvider::class);
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

    public function testClassGetInstance()
    {
        (new Bind($this->container, FakeEngine::class))->toInstance($this->engine);
        $instance = $this->container->getInstance(FakeEngine::class, Name::ANY);
        $this->assertInstanceOf(FakeEngine::class, $instance);
        $this->assertSame($this->engine, $instance);
    }

    public function testProviderGetInstance()
    {
        (new Bind($this->container, FakeEngine::class))->toProvider(FakeEngineProvider::class);
        $instance = $this->container->getInstance(FakeEngine::class, Name::ANY);
        $this->assertInstanceOf(FakeEngine::class, $instance);
    }

    public function testGetContainer()
    {
        $array = $this->container->getContainer();
        $dependencyIndex = FakeEngineInterface::class . '-' . Name::ANY;
        $this->assertArrayHasKey($dependencyIndex, $array);
    }

    public function testClassGetContainer()
    {
        (new Bind($this->container, FakeEngine::class))->toInstance($this->engine);
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
        $this->assertArrayHasKey(FakeEngineInterface::class . '-' . Name::ANY, $array);
        $this->assertArrayHasKey(FakeRobotInterface::class . '-' . Name::ANY, $array);
    }

    public function testMergePointcuts()
    {
        $extraContainer = new Container;
        $pointcut1 = new Pointcut((new Matcher)->any(), (new Matcher)->any(), [FakeDoubleInterceptor::class]);
        $pointcut2 = new Pointcut((new Matcher)->any(), (new Matcher)->any(), [FakeDoubleInterceptor::class]);
        $this->container->addPointcut($pointcut1);
        $extraContainer->addPointcut($pointcut2);
        $this->container->merge($extraContainer);
        $array = [];
        foreach ($this->container->getPointcuts() as $pointcut) {
            $array[] = $pointcut->interceptors[0];
        }
        $this->assertContains(FakeDoubleInterceptor::class, $array);
    }

    public function testMove()
    {
        $newName = 'new';
        $this->container->move(FakeEngineInterface::class, Name::ANY, FakeEngineInterface::class, $newName);
        $dependencyIndex = FakeEngineInterface::class . '-' . $newName;
        $instance = $this->container->getDependency($dependencyIndex);
        $this->assertInstanceOf(FakeEngine::class, $instance);
    }

    public function testMoveUnbound()
    {
        $this->expectException(Unbound::class);
        $this->container->move(FakeEngineInterface::class, 'invalid', FakeEngineInterface::class, 'new');
    }

    public function testAbstractClassUnbound()
    {
        try {
            $this->container->getInstance('_INVALID_INTERFACE_', Name::ANY);
        } catch (\Exception $e) {
            $this->assertSame(Unbound::class, get_class($e));
        }
    }

    public function testAnnotateConstant()
    {
        $container = new Container;
        //FakeConstantInterface
        (new Bind($container, ''))->annotatedWith(FakeConstant::class)->toInstance('kuma');
        (new Bind($container, FakeConstantConsumer::class));
        /* @var $instance FakeConstantConsumer */
        $instance = $container->getInstance(FakeConstantConsumer::class, Name::ANY);
        $this->assertSame('kuma', $instance->constantByConstruct);
        $this->assertSame('kuma', $instance->constantBySetter);
        $this->assertSame('kuma', $instance->setterConstantWithoutVarName);
        $this->assertSame('default_construct', $instance->defaultByConstruct);
        $this->assertSame('default_setter', $instance->defaultBySetter);
    }

    public function testBadMethodCall()
    {
        $this->expectException(BadMethodCallException::class);
        $container = new Container;
        //FakeConstantInterface
        (new Bind($container, FakeEngineInterface::class))->toInstance(new FakeEngine);
        $container->getInstanceWithArgs(FakeEngineInterface::class, []);
    }
}
