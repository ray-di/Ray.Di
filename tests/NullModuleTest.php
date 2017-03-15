<?php
namespace Ray\Di;

class NullModuleTest extends \PHPUnit_Framework_TestCase
{
    public function testNew()
    {
        $injector = new Injector(new NullModule);
        $this->assertInstanceOf(Injector::class, $injector);
    }
}
