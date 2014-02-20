<?php

namespace Ray\Di;

class scriptTest extends \PHPUnit_Framework_TestCase
{
    protected function setUp()
    {
        parent::setUp();
    }

    protected function tearDown()
    {
        parent::tearDown();
    }

    public function testInstance()
    {
        $instance = require $_ENV['PACKAGE_DIR'] . '/scripts/instance.php';
        $this->assertInstanceOf('Ray\Di\Injector', $instance);
    }
}
