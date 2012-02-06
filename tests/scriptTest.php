<?php
namespace Ray\Di;

/**
 * Test class for script.
 */
class scriptTest extends \PHPUnit_Framework_TestCase
{
    /**
     * Sets up the fixture, for example, opens a network connection.
     * This method is called before a test is executed.
     */
    protected function setUp()
    {
        parent::setUp();
    }

    /**
     * Tears down the fixture, for example, closes a network connection.
     * This method is called after a test is executed.
     */
    protected function tearDown()
    {
        parent::tearDown();
    }

    public function testInstance()
    {
         $instance = require_once dirname(__DIR__) . '/scripts/instance.php';
        $this->assertInstanceOf('Ray\Di\Injector', $instance);
    }
}
