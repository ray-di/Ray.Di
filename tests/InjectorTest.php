<?php
namespace Ray\Di;

use Doctrine\Common\Annotations\AnnotationReader;

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
        $this->container = new Container(new Forge(new Config(new Annotation(new Definition, new AnnotationReader))));
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
        $injector = clone $this->injector;
        $injector->getInstance('Ray\Di\Definition\LifeCycle');
        unset($injector);
        $this->assertSame('@PreDestroy', $GLOBALS['pre_destroy']);
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
     * does not expectedException Ray\Di\Exception\Binding
     * @expectedException Ray\Di\Exception\Binding
     */
    public function testNamedAnnotation()
    {
        $this->injector->setModule(new Modules\InvalidAnnotateModule);
        $instance = $this->injector->getInstance('Ray\Di\Definition\MockNamed');
        $this->assertInstanceOf('\Ray\Di\Mock\UserDb', $instance->userDb);
    }

    public function testAnnotatedWith()
    {
        $this->injector->setModule(new Modules\AnnotateModule);
        $instance = $this->injector->getInstance('Ray\Di\Definition\MockNamed');
        $this->assertInstanceOf('\Ray\Di\Mock\UserDb', $instance->userDb);
    }

    public function testAnnotatedWithAndUnannotated()
    {
        $this->injector->setModule(new Modules\AnnotateModule);
        $instance = $this->injector->getInstance('Ray\Di\Definition\MockNamed');
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
        $this->assertSame($expected, (int) $amount);
    }

    public function testBindInterceptors()
    {
        $this->injector->setModule(new Modules\AopMatcherModule);
        $instance = $this->injector->getInstance('Ray\Di\Tests\RealBillingService');
        /* @var $instance \Ray\Di\RealBillingService */
        list($amount, $unit) = $instance->chargeOrder();
        $expected = 105;
        $this->assertSame($expected, (int) $amount);
    }

    public function testBindDobuleInterceptors()
    {
        $module = new Modules\AopMatcherModule;
        $this->injector->setModule(new Modules\AopAnnotateMatcherModule);
        $instance = $this->injector->getInstance('Ray\Di\Tests\AnnotateTaxBilling');
        /* @var $instance \Ray\Di\RealBillingService */
        list($amount, $unit) = $instance->chargeOrder();
        $expected = 110;
        $this->assertSame($expected, (int) $amount);
    }

    public function testBindInterceptorsToChildClass()
    {
        $this->injector->setModule(new Modules\AopAnnotateMatcherModule);
        $instance = $this->injector->getInstance('Ray\Di\Tests\ChildRealBillingService');
        /* @var $instance \Ray\Di\RealBillingService */
        list($amount, $unit) = $instance->chargeOrder();
        $expected = 110;
        $this->assertSame($expected, (int) $amount);
    }

    public function testToString()
    {
        $this->injector->setModule(new Modules\AnnotateModule);
        $this->assertTrue(is_string((string) $this->injector));
    }

    public function testClassHint()
    {
        $this->assertTrue(is_string((string) $this->injector));
        $instance = $this->injector->getInstance('Ray\Di\Definition\ClassHint');
        $this->assertInstanceOf('\Ray\Di\Mock\Db', $instance->db);
    }

    public function testEmptyModule()
    {
        $injector = new Injector(new Container(new Forge(new Config(new Annotation(new Definition, new AnnotationReader)))));
        $ref = new \ReflectionProperty($injector, 'module');
        $ref->setAccessible(true);
        $module = $ref->getValue($injector);
        $this->assertInstanceOf('Ray\Di\EmptyModule', $module);
    }

    public function testLazyConstructParameter()
    {
        $lazyNew = $this->injector->lazyNew('Ray\Di\Mock\Db');
        $instance = $this->injector->getInstance('Ray\Di\Mock\Construct', ['db' => $lazyNew]);
        $this->assertInstanceOf('Ray\Di\Mock\Db', $instance->db);
    }

    /**
     * not expectedException Ray\Di\Exception\Binding
     *
     * @expectedException Ray\Di\Exception\Binding
     */
    public function testAbstractClassBinding()
    {
        $instance = $this->injector->getInstance('Ray\Di\Definition\AbstractBasic');
    }

    public function testConstructorBindings()
    {
        $this->injector = new Injector($this->container, new \Ray\Di\Modules\NoAnnotationBindingModule($this->injector));
        $lister = $this->injector->getInstance('Ray\Di\Mock\MovieApp\Lister');
        $this->assertInstanceOf('Ray\Di\Mock\MovieApp\Finder', $lister->finder);

    }

    /**
     */
    public function testNotBoundException()
    {
        $this->injector = new Injector($this->container, new \Ray\Di\Modules\InvalidBindingModule);
        $lister = $this->injector->getInstance('Ray\Di\Mock\MovieApp\Lister');
        $this->assertInstanceOf('Ray\Di\Mock\MovieApp\Finder', $lister->finder);
    }

    /**
     * @expectedException Ray\Di\Exception\Configuration
     */
    public function testProviderIsNotExists()
    {
        $this->injector = new Injector($this->container, new \Ray\Di\Modules\ProvideNotExistsModule);

    }

    public function test__get()
    {
        $params = $this->injector->params;
        $params['dummy'] = ['a' => 'fake1'];
        $getParams = $this->injector->params;
        $expected = $getParams['dummy'];
        $actual = $getParams['dummy'];
        $this->assertSame($actual, $expected);
    }

    /**
     * @expectedException Aura\Di\Exception\ContainerLocked
     */
    public function test_lockWithParam()
    {
        $this->injector->lock();
        $params = $this->injector->params;
    }

    /**
     * @expectedException Aura\Di\Exception\ContainerLocked
     */
    public function test_lockWhenSetModule()
    {
        $this->injector->lock();
        $this->injector->setModule(new Modules\BasicModule);
    }

    public function testConstructorBindingsWithDefault()
    {
        $this->injector = new Injector($this->container, new \Ray\Di\Modules\NoAnnotationBindingModule($this->injector));
        $constructWithDefault = $this->injector->getInstance('Ray\Di\Mock\ConstructWithDefault');
        $this->assertInstanceOf('Ray\Di\Mock\DefaultDB', $constructWithDefault->db);
    }

    public function testCreate()
    {
        $injector = Injector::create();
        $this->assertInstanceOf('Ray\Di\Injector', $injector);
    }

    public function testCreateApcOn()
    {
        $injector = Injector::create([], true);
        $this->assertInstanceOf('Ray\Di\Injector', $injector);
    }

    public function testCreateInjectorBindModule()
    {
        $injector = Injector::create([new \Ray\Di\Modules\InjectorModule]);
        $this->assertInstanceOf('Ray\Di\Injector', $injector);
    }

    public function testCreateWithModule()
    {
        $injector = Injector::create([new EmptyModule]);
        $this->assertInstanceOf('Ray\Di\Injector', $injector);
    }

    public function testCreateWithModules()
    {
        $injector = Injector::create([new EmptyModule, new EmptyModule, new EmptyModule]);
        $this->assertInstanceOf('Ray\Di\Injector', $injector);
    }

    public function testGetContainer()
    {
        $continaer = $this->injector->getContainer();
        $this->assertInstanceOf('Ray\Di\Container', $continaer);
    }

    public function testOptionalInjection()
    {
        $object = $this->injector->getInstance('Ray\Di\Definition\OptionalInject');
        $this->assertSame($object->userDb, 'NOT_INJECTED');
    }

    public function testSetLogger()
    {
        $this->injector->setLogger(new \Ray\Di\Mock\TestLogger);
        $this->injector->setModule(new Modules\BasicModule);
        $instance = $this->injector->getInstance('Ray\Di\Definition\Basic');
        $this->assertTrue(is_string(\Ray\Di\Mock\TestLogger::$log));
    }

    /**
     * @expectedException Ray\Di\Exception\Binding
     */
    public function testNoBindings()
    {
        $object = $this->injector->getInstance('Ray\Di\Definition\Basic');
    }

    /**
     * @expectedException Ray\Di\Exception\NotReadable
     */
    public function testNoClass()
    {
        $this->injector->getInstance('NotExsitsXXXXXXXXXX');
    }

    public function testGetInsntanceToClassBoundInterfacePassed()
    {
        $this->injector->setModule(new Modules\BasicModule);
        $instance = $this->injector->getInstance('Ray\Di\Mock\DbInterface');
        $this->assertInstanceOf('Ray\Di\Mock\UserDb', $instance);
    }

    public function testGetInsntanceToProviderBindindedInterfacePassed()
    {
        $this->injector->setModule(new Modules\ProviderModule);
        $instance = $this->injector->getInstance('Ray\Di\Mock\DbInterface');
        $this->assertInstanceOf('Ray\Di\Mock\UserDb', $instance);
    }

    public function testGetInstanceWithInterfaceNotLeadingBackSlash()
    {
        $this->injector->setModule(new Modules\BasicModule);
        $instance = $this->injector->getInstance('Ray\Di\Mock\DbInterface');
        $this->assertInstanceOf('\Ray\Di\Mock\UserDb', $instance);
    }

    public function testGetInstanceWithInterfaceLeadingBackSlash()
    {
        $this->injector->setModule(new Modules\BasicModule);
        $instance = $this->injector->getInstance('\Ray\Di\Mock\DbInterface');
        $this->assertInstanceOf('\Ray\Di\Mock\UserDb', $instance);
    }

    /**
     * @expectedException \Ray\Di\Exception\Binding
     */
    public function testGetInstanceWithAnnotateBindModule()
    {
        $this->injector->setModule(new Modules\AnnotateModule);
        $instance = $this->injector->getInstance('Ray\Di\Mock\DbInterface');
    }

    /**
     * @expectedException \Ray\Di\Exception\Binding
     */
    public function testNotBound()
    {
        $instance = $this->injector->getInstance('Ray\Di\Mock\DbInterface');
    }

    /**
     * @expectedException \Ray\Di\Exception\Binding
     */
    public function testNotBoundClassWithoutAnnotationinInConstructor()
    {
        $instance = $this->injector->getInstance('Ray\Di\Definition\ConstructWoAnnotation');
    }

    /**
     * @expectedException \Ray\Di\Exception\NotBound
     */
    public function testArrayTypeHint()
    {
        $this->injector->setModule(new Modules\BasicModule);
        $instance = $this->injector->getInstance('Ray\Di\Definition\ArrayType');
    }

    public function testSingletonWithModuleRequestInjection()
    {
        $module =  new Modules\RequestInjectionSingletonModule;
        $injector = new Injector($this->container, $module);
        $object = $injector->getInstance('Ray\Di\Mock\DbInterface');
        $this->assertSame(spl_object_hash($module->object), spl_object_hash($object));
    }


}
