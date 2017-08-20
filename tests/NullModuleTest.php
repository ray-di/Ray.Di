<?php
namespace Ray\Di;

use PHPUnit\Framework\TestCase;

class NullModuleTest extends TestCase
{
    public function testNew()
    {
        $injector = new Injector(new NullModule);
        $this->assertInstanceOf(Injector::class, $injector);
    }
}
