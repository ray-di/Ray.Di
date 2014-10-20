<?php

namespace Ray\Di;

use Doctrine\Common\Annotations\AnnotationReader;

class AnnotatedClassTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var AnnotatedClass
     */
    protected $annotatedClass;

    public function setUp()
    {
        parent::setUp();
        $this->annotatedClass = new AnnotatedClass(new AnnotationReader);
    }

    public function testInvoke()
    {
        $newInstance = $this->annotatedClass->__invoke(new \ReflectionClass(FakeCar::class));
        $this->assertInstanceOf(NewInstance::class, $newInstance);
        $container = new Container;
        (new Bind($container, FakeTyreInterface::class))->to(FakeTyre::class);
        (new Bind($container, FakeEngineInterface::class))->to(FakeEngine::class);
        (new Bind($container, FakeHandleInterface::class))->toProvider(FakeHandleProvider::class);
        (new Bind($container, FakeMirrorInterface::class))->annotatedWith('right')->to(FakeMirrorRight::class);
        (new Bind($container, FakeMirrorInterface::class))->annotatedWith('left')->to(FakeMirrorRight::class);
        $car = $newInstance($container);
        /** @var $car FakeCar */
        $this->assertInstanceOf(FakeCar::class, $car);
        $this->assertInstanceOf(FakeTyre::class, $car->frontTyre);
        $this->assertInstanceOf(FakeTyre::class, $car->rearTyre);
        $this->assertNull($car->hardtop);
    }
}
