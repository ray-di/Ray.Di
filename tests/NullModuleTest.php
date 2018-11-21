<?php

declare(strict_types=1);

namespace Ray\Di;

use PHPUnit\Framework\TestCase;

class NullModuleTest extends TestCase
{
    public function testEmpty()
    {
        $module = new NullModule;
        $container = $module->getContainer();
        $this->assertSame([], $container->getContainer());
    }
}
