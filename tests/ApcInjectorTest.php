<?php
namespace Ray\Di;

/**
 * Test class for Inject.
 */

class Time
{
    public $time;
    public $oclosure;

    /**
     * @Inject
     * @Named("now")
     */
    public function __construct($time)
    {
        $this->time = $time;
    }
}

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
	    $this->module = function (){ return new Modules\TimeModule; };
	}

	/**
	 * Tears down the fixture, for example, closes a network connection.
	 * This method is called after a test is executed.
	 */
	protected function tearDown()
	{
		parent::tearDown();
	}

	public function test_new(){
	    $injector = new ApcInjector([$this->module]);
		$this->assertInstanceOf('\Ray\Di\ApcInjector', $injector);
	}

	public function test_getInstance(){
	    $module = function(){ return new Modules\BasicModule;};
	    $injector = new ApcInjector([$module]);
		$instance = $injector->getInstance('Ray\Di\Definition\Basic');
		$this->assertInstanceOf('\Ray\Di\Mock\UserDb', $instance->db);
	}

	public function test_Freeze(){
	    $module = function (){ return new Modules\TimeModule; };
	    $this->injector = new ApcInjector([$module]);
		$instance1 = $this->injector->getInstance('Ray\Di\Time');
		$instance2 = $this->injector->getInstance('Ray\Di\Time');
		$this->assertSame($instance1->time, $instance2->time);
	}
}
