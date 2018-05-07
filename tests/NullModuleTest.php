<?php

declare(strict_types=1);
/**
 * This file is part of the Ray.Di package.
 *
 * @license http://opensource.org/licenses/MIT MIT
 */
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
