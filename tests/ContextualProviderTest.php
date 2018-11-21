<?php

declare(strict_types=1);

namespace Ray\Di;

use PHPUnit\Framework\TestCase;

class ContextualProviderTest extends TestCase
{
    public function testContextualProviderInjection()
    {
        $robot = (new Injector(new FakeContextualModule('main')))->getInstance(FakeRobotInterface::class);
        /* @var $robot FakeContextualRobot */
        $this->assertSame($robot->context, 'main');
    }
}
