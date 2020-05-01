<?php

declare(strict_types=1);

namespace Ray\Di;

use PHPUnit\Framework\TestCase;
use Ray\Aop\Compiler;
use Ray\Aop\Matcher;
use Ray\Aop\Pointcut;
use Ray\Aop\WeavedInterface;

class DependencyTest extends TestCase
{
    /**
     * @var Dependency
     */
    private $dependency;

    protected function setUp() : void
    {
        $class = new \ReflectionClass(FakeCar::class);
        $setters = [];
        $name = new Name(Name::ANY);
        $setters[] = new SetterMethod(new \ReflectionMethod(FakeCar::class, 'setTires'), $name);
        $setters[] = new SetterMethod(new \ReflectionMethod(FakeCar::class, 'setHardtop'), $name);
        $setterMethods = new SetterMethods($setters);
        $newInstance = new NewInstance($class, $setterMethods);
        $this->dependency = new Dependency($newInstance, new \ReflectionMethod(FakeCar::class, 'postConstruct'));
    }

    protected function tearDown() : void
    {
        parent::tearDown();
    }

    public function containerProvider()
    {
        $container = new Container;
        (new Bind($container, FakeTyreInterface::class))->to(FakeTyre::class);
        (new Bind($container, FakeEngineInterface::class))->to(FakeEngine::class);
        (new Bind($container, FakeHardtopInterface::class))->to(FakeHardtop::class);

        return [[$container]];
    }

    /**
     * @dataProvider containerProvider
     */
    public function testInject(Container $container)
    {
        $car = $this->dependency->inject($container);
        /* @var $car FakeCar */
        $this->assertInstanceOf(FakeCar::class, $car);
    }

    /**
     * @dataProvider containerProvider
     */
    public function testSetterInjection(Container $container)
    {
        $car = $this->dependency->inject($container);
        /* @var $car FakeCar */
        $this->assertInstanceOf(FakeCar::class, $car);
        $this->assertInstanceOf(FakeTyre::class, $car->frontTyre);
    }

    /**
     * @dataProvider containerProvider
     */
    public function testPostConstruct(Container $container)
    {
        $car = $this->dependency->inject($container);
        /* @var $car FakeCar */
        $this->assertTrue($car->isConstructed);
    }

    /**
     * @dataProvider containerProvider
     */
    public function testPrototype(Container $container)
    {
        $this->dependency->setScope(Scope::PROTOTYPE);
        $car1 = $this->dependency->inject($container);
        $car2 = $this->dependency->inject($container);
        $this->assertNotSame(spl_object_hash($car1), spl_object_hash($car2));
    }

    /**
     * @dataProvider containerProvider
     */
    public function testSingleton(Container $container)
    {
        $this->dependency->setScope(Scope::SINGLETON);
        $car1 = $this->dependency->inject($container);
        $car2 = $this->dependency->inject($container);
        $this->assertSame(spl_object_hash($car1), spl_object_hash($car2));
    }

    public function testInjectInterceptor()
    {
        $dependency = new Dependency(new NewInstance(new \ReflectionClass(FakeAop::class), new SetterMethods([])));
        $pointcut = new Pointcut((new Matcher)->any(), (new Matcher)->any(), [FakeDoubleInterceptor::class]);
        $dependency->weaveAspects(new Compiler($_ENV['TMP_DIR']), [$pointcut]);
        $container = new Container;
        $container->add((new Bind($container, FakeDoubleInterceptor::class))->to(FakeDoubleInterceptor::class));
        $instance = $dependency->inject($container);
        $isWeave = (new \ReflectionClass($instance))->implementsInterface(WeavedInterface::class);
        $this->assertTrue($isWeave);
        $this->assertArrayHasKey('returnSame', $instance->bindings);
    }
}
