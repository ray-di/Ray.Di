<?php

namespace Ray\Di;

class ContextualProviderTest extends \PHPUnit_Framework_TestCase
{
    public function testContextualProviderInjection()
    {
        $robot = (new Injector(new FakeContextualModule('main')))->getInstance(FakeRobotInterface::class);
        /* @var $robot FakeContextualRobot */
        $this->assertSame($robot->context, 'main');
    }
}
