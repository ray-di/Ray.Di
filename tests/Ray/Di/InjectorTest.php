<?php

namespace Ray\Di;

use Doctrine\Common\Annotations\AnnotationReader;
use Doctrine\Common\Cache\ArrayCache;
use PHPParser_PrettyPrinter_Default;
use Ray\Aop\Bind;
use Ray\Aop\Compiler;
use Ray\Di\Modules\InstanceInstallModule;
use Ray\Di\Modules\InstanceModule;
use Ray\Di\Modules\NoAnnotationBindingModule;

class InjectorTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var Injector
     */
    protected $injector;

    /**
     * @var Config
     */
    protected $config;

    /**
     * @var Container
     */
    protected $container;


    protected function setUp()
    {
        parent::setUp();
        $this->container = new Container(new Forge(new Config(new Annotation(new Definition, new AnnotationReader))));
        $this->injector = new Injector($this->container, new EmptyModule, new Bind, new Compiler($GLOBALS['TMP_DIR'], new PHPParser_PrettyPrinter_Default), new Logger);
    }

    protected function tearDown()
    {
        parent::tearDown();
    }

    public function testNewInstanceWithPostConstruct()
    {
        $mock = $this->injector->getInstance('Ray\Di\Definition\LifeCycle');
        $this->assertSame('@PostConstruct', $mock->msg);
    }

    public function testNewInstanceWithPreDestroy()
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
        $this->assertSame(['ballet', 'travel', 'php'], $instance->userFavorites);
    }

    public function testToProvider()
    {
        $this->injector->setModule(new Modules\ProviderModule);
        $instance = $this->injector->getInstance('Ray\Di\Definition\Basic');
        $this->assertInstanceOf('\Ray\Di\Mock\UserDb', $instance->db);
    }

    public function testToStringProvider()
    {
        $this->injector->setModule(new Modules\StringProviderModule);
        $instance = $this->injector->getInstance('Ray\Di\Definition\MockScalar');
        $this->assertSame('provided string', $instance->injected);
    }

    public function testToClosure()
    {
        $this->injector->setModule(new Modules\ClosureModule);
        $instance = $this->injector->getInstance('Ray\Di\Definition\Basic');
        $this->assertInstanceOf('\Ray\Di\Mock\UserDb', $instance->db);
    }

    /**
     * does not expectedException Ray\Di\Exception\Binding
     * @expectedException \Ray\Di\Exception\Binding
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

    public function testImplementedBy()
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

    public function testInjectSingleton()
    {
        $this->injector->setModule(new Modules\SingletonModule);
        $instance = $this->injector->getInstance('Ray\Di\Definition\Basic');
        $a = $instance->db->rnd;
        $instance = $this->injector->getInstance('Ray\Di\Definition\Basic');
        $b = $instance->db->rnd;
        $this->assertSame($a, $b);
    }

    public function testInjectPrototype()
    {
        $this->injector->setModule(new Modules\PrototypeModule);
        $instance = $this->injector->getInstance('Ray\Di\Definition\Basic');
        $a = $instance->db->rnd;
        $instance = $this->injector->getInstance('Ray\Di\Definition\Basic');
        $b = $instance->db->rnd;
        $this->assertFalse($a === $b);
    }

    public function testRegisterInterceptAnnotation()
    {
        $this->injector->setModule(new Modules\AopModule);
        $instance = $this->injector->getInstance('Ray\Di\Aop\RealBillingService');
        /* @var $instance \Ray\Di\Aop\RealBillingService */
        list($amount,) = $instance->chargeOrder();
        $expected = 105;
        $this->assertSame($expected, (int)$amount);
    }

    public function testBindInterceptors()
    {
        $this->injector->setModule(new Modules\AopMatcherModule);
        $instance = $this->injector->getInstance('Ray\Di\Aop\RealBillingService');
        /* @var $instance \Ray\Di\Aop\RealBillingService */
        list($amount,) = $instance->chargeOrder();
        $expected = 105;
        $this->assertSame($expected, (int)$amount);
    }

    public function testBindDoubleInterceptors()
    {
        new Modules\AopMatcherModule;
        $this->injector->setModule(new Modules\AopAnnotateMatcherModule);
        $instance = $this->injector->getInstance('Ray\Di\Aop\AnnotateTaxBilling');
        /* @var $instance \Ray\Di\Aop\AnnotateTaxBilling */
        list($amount,) = $instance->chargeOrder();
        $expected = 110;
        $this->assertSame($expected, (int)$amount);
    }

    public function testBindInterceptorsToChildClass()
    {
        $this->injector->setModule(new Modules\AopAnnotateMatcherModule);
        $instance = $this->injector->getInstance('Ray\Di\Aop\ChildRealBillingService');
        /* @var $instance \Ray\Di\Aop\ChildRealBillingService */
        list($amount,) = $instance->chargeOrder();
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
        $injector = require dirname(dirname(dirname(__DIR__))) . '/scripts/instance.php';
        $ref = new \ReflectionProperty($injector, 'module');
        $ref->setAccessible(true);
        $module = $ref->getValue($injector);
        $this->assertInstanceOf('Ray\Di\EmptyModule', $module);
    }

    /**
     * not expectedException Ray\Di\Exception\Binding
     *
     * @expectedException \Ray\Di\Exception\Binding
     */
    public function testAbstractClassBinding()
    {
        $this->injector->getInstance('Ray\Di\Definition\AbstractBasic');
    }

    public function testConstructorBindings()
    {
        $this->injector->setModule(new NoAnnotationBindingModule($this->injector));
        $lister = $this->injector->getInstance('Ray\Di\Mock\MovieApp\Lister');
        $this->assertInstanceOf('Ray\Di\Mock\MovieApp\Finder', $lister->finder);

    }

    public function testNotBoundException()
    {
        $this->injector->setModule(new Modules\InvalidBindingModule);
        $lister = $this->injector->getInstance('Ray\Di\Mock\MovieApp\Lister');
        $this->assertInstanceOf('Ray\Di\Mock\MovieApp\Finder', $lister->finder);
    }

    /**
     * @expectedException \Ray\Di\Exception\InvalidProvider
     */
    public function testProviderIsNotExists()
    {
        $this->injector = new Injector($this->container, new Modules\ProvideNotExistsModule, new Bind, new Compiler($GLOBALS['TMP_DIR'], new PHPParser_PrettyPrinter_Default), new Logger);

    }

    public function testConstructorBindingsWithDefault()
    {
        $this->injector->setModule(new NoAnnotationBindingModule($this->injector));
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
        $injector = Injector::create([], new ArrayCache);
        $this->assertInstanceOf('Ray\Di\Injector', $injector);
    }

    public function testCreateInjectorBindModule()
    {
        $injector = Injector::create([new Modules\InjectorModule]);
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
        $container = $this->injector->getContainer();
        $this->assertInstanceOf('Ray\Di\Container', $container);
    }

    public function testOptionalInjection()
    {
        $object = $this->injector->getInstance('Ray\Di\Definition\OptionalInject');
        $this->assertSame($object->userDb, 'NOT_INJECTED');
    }

    /**
     * @expectedException \Ray\Di\Exception\Binding
     */
    public function testNoBindings()
    {
        $this->injector->getInstance('Ray\Di\Definition\Basic');
    }

    /**
     * @expectedException \Ray\Di\Exception\NotReadable
     */
    public function testNoClass()
    {
        $this->injector->getInstance('NotExistsXXXXXXXXXX');
    }

    public function testGetInstanceToClassBoundInterfacePassed()
    {
        $this->injector->setModule(new Modules\BasicModule);
        $instance = $this->injector->getInstance('Ray\Di\Mock\DbInterface');
        $this->assertInstanceOf('Ray\Di\Mock\UserDb', $instance);
    }

    public function testGetInstanceToProviderBoundInterfacePassed()
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
        $this->injector->getInstance('Ray\Di\Mock\DbInterface');
    }

    /**
     * @expectedException \Ray\Di\Exception\Binding
     */
    public function testNotBound()
    {
        $this->injector->getInstance('Ray\Di\Mock\DbInterface');
    }

    /**
     * @expectedException \Ray\Di\Exception\Binding
     */
    public function testNotBoundClassWithoutAnnotationInConstructor()
    {
        $this->injector->getInstance('Ray\Di\Definition\ConstructWoAnnotation');
    }

    /**
     * @expectedException \Ray\Di\Exception\NotBound
     */
    public function testArrayTypeHint()
    {
        $this->injector->setModule(new Modules\BasicModule);
        $this->injector->getInstance('Ray\Di\Definition\ArrayType');
    }

    public function testSingletonWithModuleRequestInjection()
    {
        $module = new Modules\RequestInjectionSingletonModule;
        $this->injector->setModule($module);
        $object = $this->injector->getInstance('Ray\Di\Mock\DbInterface');
        $this->assertSame(spl_object_hash($module->object), spl_object_hash($object));
    }

    /**
     * @expectedException \Ray\Di\Exception\NotBound
     */
    public function testNotInstantiableException()
    {
        $this->injector->getInstance('Ray\Di\Mock\AbstractDb');
    }

    public function testBoundInstance()
    {
        $user = Injector::create([new InstanceModule])->getInstance('Ray\Di\Mock\UserInterface');
        $this->assertInstanceOf('Ray\Di\Mock\User', $user);
    }

    public function testInstallDuplication()
    {
        $module = (new InstanceInstallModule);
        $module->activate();
        $actual = ((array)$module->bindings['']['id']['to']);
        $expected = ['instance', 'PC6001'];
        $this->assertSame($expected, $actual);
    }

    public function testLazyParam()
    {
        $this->injector->getContainer()->params['Ray\Di\Mock\MovieApp\Lister'] = [
            'finder' => $this->injector->getContainer()->lazyNew('Ray\Di\Mock\MovieApp\Finder')
        ];
        $lister = $this->injector->getInstance('Ray\Di\Mock\MovieApp\Lister');
        $this->assertInstanceOf('Ray\Di\Mock\MovieApp\Lister', $lister);
    }

    public function testGetLoggerForIterator()
    {
        $this->injector->setModule(new Modules\BasicModule);
        $this->injector->getInstance('Ray\Di\Definition\Basic');
        $logger = $this->injector->getLogger();
        $classes = [];
        foreach ($logger as $log) {
            list($class, , , ,) = $log;
            $classes[] = $class;
        }
        $this->assertSame($classes, ['Ray\Di\Mock\UserDb', 'Ray\Di\Definition\Basic']);
    }

    public function testGetLoggerForString()
    {
        $this->injector->setModule(new Modules\BasicModule);
        $this->injector->getInstance('Ray\Di\Definition\Basic');
        $logger = $this->injector->getLogger();
        $this->assertInternalType('string', (string)$logger);
    }

    public function testGetLoggerForStringSingleton()
    {
        $this->injector->setModule(new Modules\UseBasicModule);
        $this->injector->getInstance('Ray\Di\Definition\UseBasic');
        $logger = $this->injector->getLogger();
        $expected = 'class:Ray\Di\Definition\Basic setDb:Ray\Di\Mock\UserDb#prototype' . PHP_EOL;
        $expected .= 'class:Ray\Di\Definition\UseBasic setBasic1:Ray\Di\Definition\Basic#prototype setBasic2:Ray\Di\Definition\Basic#singleton, Ray\Di\Definition\Basic#singleton';
        $this->assertContains($expected, (string)$logger);
    }
    public function testCircularBindings()
    {
        $this->injector->setModule(new Modules\InterfaceBindModule);
        $instance = $this->injector->getInstance('Ray\Di\Mock\ChildDbInterface');
        $this->assertInstanceOf('\Ray\Di\Mock\UserDb', $instance);
    }

    public function testInjectOnce()
    {
        $this->injector->setModule(new Modules\BasicModule);
        $instance = $this->injector->getInstance('Ray\Di\Definition\InjectOnce');
        /** @var $instance \Ray\Di\Definition\InjectOnce */
        $this->assertSame(1, $instance->count);
    }

    public function testSerialize()
    {
        $this->injector->setModule(new Modules\BasicModule);
        $injector = unserialize(serialize($this->injector));
        $instance = $injector->getInstance('Ray\Di\Definition\Basic');
        $this->assertInstanceOf('\Ray\Di\Mock\UserDb', $instance->db);
    }

    public function testInjectConstructorUsingProvider()
    {
        $this->injector->setModule(new Modules\ConstructorModule);
        $instance = $this->injector->getInstance('Ray\Di\Mock\ConcreteClass3RequiresConcreteClass2');

        $this->assertInstanceOf('\Ray\Di\Mock\ConcreteClassWithoutConstructor', $instance->object->object);
    }
}
