<?php
namespace Ray\Di;

/**
 * Test class for Inject.
 */

class InjectorTest extends \PHPUnit_Framework_TestCase
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
        $this->container = new Container(new Forge(new Config(new Annotation(new Definition))));
        $this->injector = new Injector($this->container, new EmptyModule);
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
        $this->assertSame('@PreDestroy', $GLOBALS['pre_destoroy']);
    }

    public function testToClass()
    {
        $this->injector->setModule(new Modules\BasicModule);
        $instance = $this->injector->getInstance('Ray\Di\Definition\Basic');
        $this->assertInstanceOf('\Ray\Di\Mock\UserDb', $instance->db);
    }

    public function testToInstance()
    {

        $this->injector->setModule(new Modules\InstanceModule);
        $instance = $this->injector->getInstance('Ray\Di\Definition\Basic');
        $this->assertInstanceOf('\Ray\Di\Mock\UserDb', $instance->db);
    }

    public function testToInstanceWithScalar()
    {
        $this->injector->setModule(new Modules\InstanceModule);
        $instance = $this->injector->getInstance('Ray\Di\Definition\Instance');
        $this->assertSame('PC6001', $instance->userId);
        $this->assertSame('koriym', $instance->name);
        $this->assertSame(21, $instance->age);
        $this->assertSame('male', $instance->gender);
    }

    public function testToProvider()
    {
        $this->injector->setModule(new Modules\ProviderModule);
        $instance = $this->injector->getInstance('Ray\Di\Definition\Basic');
        $this->assertInstanceOf('\Ray\Di\Mock\UserDb', $instance->db);
    }

    public function testToClosure()
    {
        $this->injector->setModule(new Modules\ClosureModule);
        $instance = $this->injector->getInstance('Ray\Di\Definition\Basic');
        $this->assertInstanceOf('\Ray\Di\Mock\UserDb', $instance->db);
    }

    /**
     * does not expectedException Ray\Di\Exception\InvalidBinding
     * @expectedException Ray\Di\Exception\InvalidBinding
     */
    public function testInvalidNamedAnnotation()
    {
        $this->injector->setModule(new Modules\InvalidAnnotateModule);
        $instance = $this->injector->getInstance('Ray\Di\Definition\Named');
        $this->assertInstanceOf('\Ray\Di\Mock\UserDb', $instance->userDb);
    }

    public function testAnnotatedWith()
    {
        $this->injector->setModule(new Modules\AnnotateModule);
        $instance = $this->injector->getInstance('Ray\Di\Definition\Named');
        $this->assertInstanceOf('\Ray\Di\Mock\UserDb', $instance->userDb);
    }

    public function testAnnotatedWithAndUnannotated()
    {
        $this->injector->setModule(new Modules\AnnotateModule);
        $instance = $this->injector->getInstance('Ray\Di\Definition\Named');
        $this->assertInstanceOf('\Ray\Di\Mock\UserDb', $instance->userDb);
    }

    public function testMultiInject()
    {
        $this->injector->setModule(new Modules\MultiModule);
        $instance = $this->injector->getInstance('Ray\Di\Definition\Multi');
        $this->assertInstanceOf('\Ray\Di\Mock\UserDb', $instance->userDb);
    }

    public function testConstructorInjection()
    {
        $this->injector->setModule(new Modules\BasicModule);
        $instance = $this->injector->getInstance('Ray\Di\Definition\Construct');
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
        $this->injector->setModule(new Modules\SingletonModule);
        $instance = $this->injector->getInstance('Ray\Di\Definition\Basic');
        $a = $instance->db->rnd;
        $instance = $this->injector->getInstance('Ray\Di\Definition\Basic');
        $b = $instance->db->rnd;
        $this->assertSame($a, $b);
    }

    public function testInjectProtortype()
    {
        $this->injector->setModule(new Modules\PrototypeModule);
        $instance = $this->injector->getInstance('Ray\Di\Definition\Basic');
        $a = $instance->db->rnd;
        $instance = $this->injector->getInstance('Ray\Di\Definition\Basic');
        $b = $instance->db->rnd;
        $this->assertFalse($a === $b);
    }

    public function estregisterInterceptAnnotation()
    {
        $this->injector->setModule(new Modules\AopModule);
        $instance = $this->injector->getInstance('Ray\Di\Tests\RealBillingService');
        /* @var $instance \Ray\Di\RealBillingService */
        list($amount, $unit) = $instance->chargeOrder();
        $expected = 105;
        $this->assertSame($expected, (int)$amount);
    }

    public function testBindInterceptors()
    {
        $this->injector->setModule(new Modules\AopMatcherModule);
        $instance = $this->injector->getInstance('Ray\Di\Tests\RealBillingService');
        /* @var $instance \Ray\Di\RealBillingService */
        list($amount, $unit) = $instance->chargeOrder();
        $expected = 105;
        $this->assertSame($expected, (int)$amount);
    }

    public function testBindDobuleInterceptors()
    {
        $module = new Modules\AopMatcherModule;
        $this->injector->setModule(new Modules\AopAnnotateMatcherModule);
        $instance = $this->injector->getInstance('Ray\Di\Tests\AnnotateTaxBilling');
        /* @var $instance \Ray\Di\RealBillingService */
        list($amount, $unit) = $instance->chargeOrder();
        $expected = 110;
        $this->assertSame($expected, (int)$amount);
    }

    public function testBindInterceptorsToChildClass()
    {
        $this->injector->setModule(new Modules\AopAnnotateMatcherModule);
        $instance = $this->injector->getInstance('Ray\Di\Tests\ChildRealBillingService');
        /* @var $instance \Ray\Di\RealBillingService */
        list($amount, $unit) = $instance->chargeOrder();
        $expected = 110;
        $this->assertSame($expected, (int)$amount);
    }

    public function testToString()
    {
        $this->injector->setModule(new Modules\AnnotateModule);
        $this->assertTrue(is_string((string)$this->injector));
    }

    public function testClassHint()
    {
        $this->assertTrue(is_string((string)$this->injector));
        $instance = $this->injector->getInstance('Ray\Di\Definition\ClassHint');
        $this->assertInstanceOf('\Ray\Di\Mock\Db', $instance->db);
    }

    public function testEmptyModule()
    {
        $injector = new Injector(new Container(new Forge(new Config(new Annotation(new Definition)))));
        $ref = new \ReflectionProperty($injector, 'module');
        $ref->setAccessible(true);
        $module = $ref->getValue($injector);
        $this->assertInstanceOf('Ray\Di\EmptyModule', $module);
    }

    public function testLazyConstructParameter()
    {
        $lazyNew = $this->injector->getContainer()->lazyNew('Ray\Di\Mock\Db');
        $instance = $this->injector->getInstance('Ray\Di\Mock\Construct', array('db' => $lazyNew));
        $this->assertInstanceOf('Ray\Di\Mock\Db', $instance->db);
    }

    /**
     * not expectedException Ray\Di\Exception\InvalidBinding
     *
     * @expectedException Ray\Di\Exception\InvalidBinding
     */
    public function testAbstractClassInvalidBinding()
    {
        $instance = $this->injector->getInstance('Ray\Di\Definition\AbstractBasic');
    }

}
