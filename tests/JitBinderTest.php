<?php

namespace Ray\Di;

use Doctrine\Common\Cache\ArrayCache;

class JitBinderTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var JitBinder
     */
    private $jitBinder;

    /**
     * @var Container
     */
    private $container;

    public function setUp()
    {
        $this->container = new Container;
        $this->jitBinder = new JitBinder($this->container, new ArrayCache, $_ENV['TMP_DIR']);
    }

    public function testBind()
    {
        $this->jitBinder->bind(FakeRobotTeam::class);
        $dependency = $this->container->getContainer()['Ray\Di\FakeRobotTeam-*'];
        $this->assertInstanceOf(Dependency::class, $dependency);
    }

    public function testBindTwice()
    {
        $this->jitBinder->bind(FakeRobotTeam::class);
        $this->jitBinder->bind(FakeRobotTeam::class);
        $dependency = $this->container->getContainer()['Ray\Di\FakeRobotTeam-*'];
        $this->assertInstanceOf(Dependency::class, $dependency);
    }
}
