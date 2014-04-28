<?php

namespace Ray\Di;

use Doctrine\Common\Cache\ArrayCache;
use Ray\Di\Mock\AnnotatedSingleton;
use Ray\Di\Mock\RndDb;
use Ray\Di\Mock\SingletonConsumer;

class InjectorDiCompilerSingletonTest extends \PHPUnit_Framework_TestCase
{
    protected function setUp()
    {
        SingletonConsumer::$instances = [];
        AnnotatedSingleton::$number = 0;
    }

    public function SingletonModuleProvider()
    {
        return [
            [Injector::create([new Modules\SingletonModule])],
            [DiCompiler::create(function() {return new Modules\SingletonModule;}, new ArrayCache, __METHOD__, $_ENV['TMP_DIR'])]
        ];
    }

    /**
     * @dataProvider SingletonModuleProvider
     */
    public function testInSingletonInterface(InstanceInterface $injector)
    {
        $dbInstance1 = $injector->getInstance('Ray\Di\Mock\DbInterface');
        $dbInstance2 = $injector->getInstance('Ray\Di\Mock\DbInterface');
        $a = spl_object_hash($dbInstance1);
        $b = spl_object_hash($dbInstance2);
        $this->assertSame($a, $b);
    }

    public function SingletonProviderModuleProvider()
    {
        return [
            [Injector::create([new Modules\SingletonModule])],
            [DiCompiler::create(function() {return new Modules\SingletonModule;}, new ArrayCache, __METHOD__, $_ENV['TMP_DIR'])]
        ];
    }

    /**
     * @dataProvider SingletonProviderModuleProvider
     */
    public function testInSingletonByProviderInterface(InstanceInterface $injector)
    {
        $dbInstance1 = $injector->getInstance('Ray\Di\Mock\DbInterface');
        $dbInstance2 = $injector->getInstance('Ray\Di\Mock\DbInterface');
        $a = spl_object_hash($dbInstance1);
        $b = spl_object_hash($dbInstance2);
        $this->assertSame($a, $b);
        $this->assertSame('Ray\Di\Mock\RndDb', $dbInstance1->madeBy);
    }

    public function SingletonByProviderInterfaceModuleProvider()
    {
        return [
            [Injector::create([new Modules\SingletonProviderForClassModule])],
            [DiCompiler::create(function() {return new Modules\SingletonProviderForClassModule;}, new ArrayCache, __METHOD__, $_ENV['TMP_DIR'])]
        ];
    }

    /**
     * @dataProvider SingletonByProviderInterfaceModuleProvider
     *
     * @return \Ray\Di\Mock\RndDb
     */
    public function testInSingletonByProviderClass(InstanceInterface $injector)
    {
        $dbInstance1 = $injector->getInstance('Ray\Di\Mock\RndDb');
        $dbInstance2 = $injector->getInstance('Ray\Di\Mock\RndDb');
        $a = spl_object_hash($dbInstance1);
        $b = spl_object_hash($dbInstance2);
        $this->assertSame($a, $b);
        $this->assertSame('Ray\Di\Mock\RndDbProvider::get', $dbInstance1->madeBy);

        return $dbInstance1;
    }

    /**
     * @dataProvider SingletonByProviderInterfaceModuleProvider
     *
     * @return RndDb
     */
    public function testConsumerAskSingletonByClass(InstanceInterface $injector)
    {
        $consumer = $injector->getInstance('Ray\Di\Mock\RndDbConsumer');
        /* @var $consumer \Ray\Di\Mock\RndDbConsumer */
        $a = spl_object_hash($consumer->db1);
        $b = spl_object_hash($consumer->db2);
        $this->assertSame($a, $b);
        $this->assertSame('Ray\Di\Mock\RndDbProvider::get', $consumer->db1->madeBy);
    }

    /**
     * @dataProvider SingletonByProviderInterfaceModuleProvider
     */
    public function testSerializedObjectSingleton(InstanceInterface $injector)
    {
        $instance = $injector->getInstance('Ray\Di\Mock\RndDbConsumer');
        $consumer = unserialize(serialize($instance));
        $a = spl_object_hash($consumer->db1);
        $b = spl_object_hash($consumer->db2);
        $this->assertSame($a, $b);
    }

    /**
     * @dataProvider SingletonByProviderInterfaceModuleProvider
     */
    public function testSerializedInjectorSingleton(InstanceInterface $injector)
    {
        $instance = $injector->getInstance('Ray\Di\Mock\RndDbConsumer');
        $consumer = unserialize(serialize($instance));
        $a = spl_object_hash($consumer->db1);
        $b = spl_object_hash($consumer->db2);
        $this->assertSame($a, $b);
    }

    public function SingletonAnnotationModuleProvider()
    {
        return [
            [Injector::create([new Modules\SingletonAnnotationModule])],
            [DiCompiler::create(function() {return new Modules\SingletonAnnotationModule;}, new ArrayCache, __METHOD__, $_ENV['TMP_DIR'])]
        ];
    }

    /**
     * @dataProvider SingletonAnnotationModuleProvider
     */
    public function testInSingletonInterfaceWithAnnotation(InstanceInterface $injector)
    {
        $dbInstance1 = $injector->getInstance('Ray\Di\Mock\SingletonDbInterface');
        $dbInstance2 = $injector->getInstance('Ray\Di\Mock\SingletonDbInterface');
        $a = spl_object_hash($dbInstance1);
        $b = spl_object_hash($dbInstance2);
        $this->assertSame($a, $b);
    }

    public function SingletonModuleInjectorProvider()
    {
        return [
            [Injector::create([new Modules\SingletonModule])],
            [DiCompiler::create(function() {return new Modules\SingletonModule;}, new ArrayCache, __METHOD__, $_ENV['TMP_DIR'])]
        ];
    }

    /**
     * @dataProvider SingletonModuleInjectorProvider
     */
    public function testInjectInSingletonInterface(InstanceInterface $injector)
    {
        $numberInstance1 = $injector->getInstance('Ray\Di\Mock\Number');
        $numberInstance2 = $injector->getInstance('Ray\Di\Mock\Number');
        $a = spl_object_hash($numberInstance1->db);
        $b = spl_object_hash($numberInstance2->db);
        $this->assertSame($a, $b);
    }

    public function SingletonAnnotationModuleInjectorProvider()
    {
        return [
            [Injector::create([new Modules\SingletonAnnotationModule])],
            [DiCompiler::create(function() {return new Modules\SingletonAnnotationModule;}, new ArrayCache, __METHOD__, $_ENV['TMP_DIR'])]
        ];
    }


    /**
     * @dataProvider SingletonAnnotationModuleInjectorProvider
     */
    public function testInjectInSingletonInterfaceWithAnnotation(InstanceInterface $injector)
    {
        $numberInstance1 = $injector->getInstance('Ray\Di\Mock\SingletonNumber');
        $numberInstance2 = $injector->getInstance('Ray\Di\Mock\SingletonNumber');
        $a = spl_object_hash($numberInstance1->db);
        $b = spl_object_hash($numberInstance2->db);
        $this->assertSame($a, $b);
    }

    /**
     * @dataProvider SingletonModuleInjectorProvider
     */
    public function testInjectInSingletonInterface4times(InstanceInterface $injector)
    {
        $numberInstance1 = $injector->getInstance('Ray\Di\Mock\DbInterface');
        $numberInstance2 = $injector->getInstance('Ray\Di\Mock\DbInterface');
        $numberInstance3 = $injector->getInstance('Ray\Di\Mock\DbInterface');
        $numberInstance4 = $injector->getInstance('Ray\Di\Mock\DbInterface');

        $result1 = spl_object_hash($numberInstance1);
        $result2 = spl_object_hash($numberInstance2);
        $result3 = spl_object_hash($numberInstance3);
        $result4 = spl_object_hash($numberInstance4);
        $this->assertSame($result1, $result2);
        $this->assertSame($result2, $result3);
        $this->assertSame($result3, $result4);
    }

    /**
     * @dataProvider SingletonModuleInjectorProvider
     */
    public function testThatConsumerIsNotConstructedMoreThanOnce(InstanceInterface $injector)
    {
        $injector->getInstance( 'Ray\Di\Mock\SingletonConsumer' ); //One SingletonConsumer should exist.

        $numberOfSingletonConsumers = count( SingletonConsumer::$instances );
        $numberOfSingletonConsumersThatShouldBeConstructed = 1;

        // the number of consumer can not be trusted in compile.
        // $this->assertEquals( $numberOfSingletonConsumersThatShouldBeConstructed, $numberOfSingletonConsumers );

        $injector->getInstance( 'Ray\Di\Mock\SingletonConsumer' ); //Two SingletonConsumer should exist.

        $numberOfSingletonConsumers = count( SingletonConsumer::$instances );
        $numberOfSingletonConsumersThatShouldBeConstructed = 2;

        $this->assertEquals( $numberOfSingletonConsumersThatShouldBeConstructed, $numberOfSingletonConsumers );
    }

    /**
     * @dataProvider SingletonModuleInjectorProvider
     */
    public function testThatAnnotatedSingletonIsNotConstructedMoreThanOnce(InstanceInterface $injector)
    {
        $injector->getInstance( 'Ray\Di\Mock\SingletonConsumer' ); //One AnnotatedSingleton should exist.

        $numberOfSingletonInstances = AnnotatedSingleton::$number;
        $numberOfTimesSingletonsShouldBeConstructed = 1;

        $this->assertEquals( $numberOfTimesSingletonsShouldBeConstructed, $numberOfSingletonInstances );

        $injector->getInstance( 'Ray\Di\Mock\SingletonConsumer' ); //One AnnotatedSingleton should exist.

        $numberOfSingletonInstances = AnnotatedSingleton::$number;
        $numberOfTimesSingletonsShouldBeConstructed = 1;

        $this->assertEquals( $numberOfTimesSingletonsShouldBeConstructed, $numberOfSingletonInstances );
    }
}
