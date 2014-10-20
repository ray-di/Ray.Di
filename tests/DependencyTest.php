<?php

namespace Ray\Di;

class DependencyTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var Dependency
     */
    private $dependency;

    public function setUp()
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

    /**
     * @return Container
     */
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
        /** @var $car FakeCar */
        $this->assertInstanceOf(FakeCar::class, $car);
    }

    /**
     * @dataProvider containerProvider
     */
    public function testSetterInjection(Container $container)
    {
        $car = $this->dependency->inject($container);
        /** @var $car FakeCar */
        $this->assertInstanceOf(FakeCar::class, $car);
        $this->assertInstanceOf(FakeTyre::class, $car->frontTyre);
    }

    /**
     * @dataProvider containerProvider
     */
    public function testPostConstruct(Container $container)
    {
        $car = $this->dependency->inject($container);
        /** @var $car FakeCar */
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
}
 