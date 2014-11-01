<?php

namespace Ray\Di;

use Ray\Di\Exception\InvalidBind;
use Ray\Di\Exception\NotFound;

class ModuleTest extends \PHPUnit_Framework_TestCase
{
    public function testNew()
    {
        $module = new FakeInstanceBindModule;
        $this->assertInstanceOf(AbstractModule::class, $module);
    }

    public function testInstall()
    {
        $module = new FakeInstallModule;
        $this->assertInstanceOf(AbstractModule::class, $module);
    }

    public function testToInvalidClass()
    {
        $this->setExpectedException(NotFound::class);
        new FakeToBindInvalidClassModule;
    }
}
