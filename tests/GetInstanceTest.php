<?php
namespace Ray\Di;

use Ray\Aop\Bind;

/**
 * Test class for SingletonModule.
 */
class GetInstanceTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var Injector
     */
    protected $injector;

    /**
     * Sets up the fixture, for example, opens a network connection.
     * This method is called before a test is executed.
     */
    protected function setUp()
    {
        parent::setUp();
        $this->injector = Injector::create([new Modules\SingletonModule()], false);
    }

    /**
     * Tears down the fixture, for example, closes a network connection.
     * This method is called after a test is executed.
     */
    protected function tearDown()
    {
        parent::tearDown();
    }

    public function testInSingletonInterface()
    {
        $dbInstance1 = $this->injector->getInstance('Ray\Di\Mock\DbInterface');
        $dbInstance2 = $this->injector->getInstance('Ray\Di\Mock\DbInterface');

        $a = spl_object_hash($dbInstance1);
        $b = spl_object_hash($dbInstance2);
        $this->assertSame($a, $b);
    }
}
