<?php

declare(strict_types=1);
/**
 * This file is part of the Ray.Di package.
 *
 * @license http://opensource.org/licenses/MIT MIT
 */
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
