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
}
