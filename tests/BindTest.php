<?php

namespace Ray\Di;

use Ray\Di\Exception\NotFound;

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
        $this->bind->to('Invalid-class');
    }

    public function testInvalidToProviderTest()
    {
        $this->setExpectedException(Notfound::class);
        $this->bind->toProvider('Invalid-class');
    }
}
