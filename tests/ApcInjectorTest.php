<?php
namespace Ray\Di;

use Doctrine\Common\Annotations\AnnotationReader;

/**
 * Test class for Inject.
 */

class ApcInjectorTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var Injector
     */
    protected $injector;

    protected $config;

    /**
     * Sets up the fixture, for example, opens a network connection.
     * This method is called before a test is executed.
     */
    protected function setUp()
    {
        parent::setUp();
        $this->container = new Container(new Forge(new Config(new Annotation(new Definition, new AnnotationReader))));
    }

    /**
     * Tears down the fixture, for example, closes a network connection.
     * This method is called after a test is executed.
     */
    protected function tearDown()
    {
        parent::tearDown();
    }

    public function test_new()
    {
        $injector = new ApcInjector($this->container, new Modules\TimeModule);
        $this->assertInstanceOf('\Ray\Di\ApcInjector', $injector);
    }

    public function test_getInstance()
    {
        $injector = new ApcInjector($this->container, new Modules\BasicModule);
        $instance = $injector->getInstance('Ray\Di\Definition\Basic');
        $this->assertInstanceOf('\Ray\Di\Mock\UserDb', $instance->db);
    }

    public function test_Freeze()
    {
        $this->injector = new ApcInjector($this->container, new Modules\TimeModule);
        $instance1 = $this->injector->getInstance('Ray\Di\Mock\Time');
        $instance2 = $this->injector->getInstance('Ray\Di\Mock\Time');
        $this->assertSame($instance1->time, $instance2->time);
    }
}
