<?php
namespace Ray\Di;

/**
 * Test class for Inject.
 */
use Ray\Di\Modules\EmptyModule;

class InjectTest extends \PHPUnit_Framework_TestCase
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
        $this->container = new Container(new Forge(new Config(new Annotation)));
        $this->injector = new Injector($this->container, new EmptyModule());
    }

    /**
     * Tears down the fixture, for example, closes a network connection.
     * This method is called after a test is executed.
     */
    protected function tearDown()
    {
        parent::tearDown();
    }

    public function testNewInstanceWithPostConstruct()
    {
        $mock = $this->injector->getInstance('Ray\Di\Definition\LifeCycle');
        $this->assertSame('@PostConstruct', $mock->msg);
    }

    public function testNewInstanceWithPreDestory()
    {
        $mock = $this->injector->getInstance('Ray\Di\Definition\LifeCycle');
        unset($this->injector);
        $this->assertSame('@PreDestoroy', $GLOBALS['pre_destoroy']);
    }

    public function testToClass()
    {
        $injector = new Injector($this->container, new Modules\BasicModule);
        $instance = $injector->getInstance('Ray\Di\Definition\Basic');
        $this->assertInstanceOf('\Ray\Di\Mock\UserDb', $instance->db);
    }

    public function testToInstance()
    {

        $injector = new Injector($this->container, new Modules\InstanceModule);
        $instance = $injector->getInstance('Ray\Di\Definition\Basic');
        $this->assertInstanceOf('\Ray\Di\Mock\UserDb', $instance->db);
    }

    public function testToInstanceWithScalar()
    {
        $injector = new Injector($this->container, new Modules\InstanceModule);
        $instance = $injector->getInstance('Ray\Di\Definition\Instance');
        $this->assertSame('PC6001', $instance->userId);
        $this->assertSame('koriym', $instance->name);
        $this->assertSame(21, $instance->age);
        $this->assertSame('male', $instance->gender);
    }

    public function testToProvider()
    {
        $injector = new Injector($this->container,  new Modules\ProviderModule);
        $instance = $injector->getInstance('Ray\Di\Definition\Basic');
        $this->assertInstanceOf('\Ray\Di\Mock\UserDb', $instance->db);
    }

    public function testToClosure()
    {
        $injector = new Injector($this->container, new Modules\ClosureModule);
        $instance = $injector->getInstance('Ray\Di\Definition\Basic');
        $this->assertInstanceOf('\Ray\Di\Mock\UserDb', $instance->db);
    }

    /**
     * @expectedException Ray\Di\Exception\InvalidBinding
     */
    public function testInvalidNamedAnnotation()
    {
        $injector = new Injector($this->container, new Modules\InvalidAnnotateModule);
        $instance = $injector->getInstance('Ray\Di\Definition\Named');
        $this->assertInstanceOf('\Ray\Di\Mock\UserDb', $instance->userDb);
    }

    public function testAnnotatedWith()
    {
        $injector = new Injector($this->container, new Modules\AnnotateModule);
        $instance = $injector->getInstance('Ray\Di\Definition\Named');
        $this->assertInstanceOf('\Ray\Di\Mock\UserDb', $instance->userDb);
    }

    public function testAnnotatedWithAndUnannotated()
    {
        $injector = new Injector($this->container, new Modules\AnnotateModule);
        $instance = $injector->getInstance('Ray\Di\Definition\Named');
        $this->assertInstanceOf('\Ray\Di\Mock\UserDb', $instance->userDb);
    }

    public function testMultiInject()
    {
        $injector = new Injector($this->container, new Modules\MultiModule);
        $instance = $injector->getInstance('Ray\Di\Definition\Multi');
        $this->assertInstanceOf('\Ray\Di\Mock\UserDb', $instance->userDb);
    }

    public function testConstructorInjection()
    {
        $injector = new Injector($this->container, new Modules\BasicModule);
        $instance = $injector->getInstance('Ray\Di\Definition\Construct');
        $this->assertInstanceOf('\Ray\Di\Mock\UserDb', $instance->db);
    }

    public function testImplemetedBy()
    {
        $instance = $this->injector->getInstance('Ray\Di\Definition\Implemented');
        $this->assertInstanceOf('\Ray\Di\Mock\Log', $instance->log);
    }

    public function testProvidedBy()
    {
        $instance = $this->injector->getInstance('Ray\Di\Definition\Provided');
        $this->assertInstanceOf('\Ray\Di\Mock\Reader', $instance->reader);
    }

    public function testClone()
    {
        $clone = clone $this->injector;
        $this->assertNotSame($clone, $this->injector);
        $this->assertNotSame($clone->getContainer(), $this->injector->getContainer());
    }

    public function testInjectSigleton()
    {
        $injector = new Injector($this->container, new Modules\SingletonModule);
        $instance = $injector->getInstance('Ray\Di\Definition\Basic');
        $a = $instance->db->rnd;
        $instance = $injector->getInstance('Ray\Di\Definition\Basic');
        $b = $instance->db->rnd;
        $this->assertSame($a, $b);
    }

    public function testInjectProtortype()
    {
        $injector = new Injector($this->container, new Modules\PrototypeModule);
        $instance = $injector->getInstance('Ray\Di\Definition\Basic');
        $a = $instance->db->rnd;
        $instance = $injector->getInstance('Ray\Di\Definition\Basic');
        $b = $instance->db->rnd;
        $this->assertFalse($a === $b);
    }
}