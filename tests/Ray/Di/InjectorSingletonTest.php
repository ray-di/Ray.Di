<?php
namespace Ray\Di;

use Doctrine\Common\Cache\FilesystemCache;
use Ray\Di\Mock\RndDb;

/**
 * Test class for SingletonModule.
 */
class GetInstanceTest extends \PHPUnit_Framework_TestCase
{
    protected function setUp()
    {
        parent::setUp();
    }

    protected function tearDown()
    {
        parent::tearDown();
    }

    public function testInSingletonInterface()
    {
        $injector = Injector::create([new Modules\SingletonModule()]);

        $dbInstance1 = $injector->getInstance('Ray\Di\Mock\DbInterface');
        $dbInstance2 = $injector->getInstance('Ray\Di\Mock\DbInterface');

        $a = spl_object_hash($dbInstance1);
        $b = spl_object_hash($dbInstance2);
        $this->assertSame($a, $b);
    }

    public function testInSingletonByProviderInterface()
    {
        $injector = Injector::create([new Modules\SingletonProviderModule()]);

        $dbInstance1 = $injector->getInstance('Ray\Di\Mock\DbInterface');
        $dbInstance2 = $injector->getInstance('Ray\Di\Mock\DbInterface');

        $a = spl_object_hash($dbInstance1);
        $b = spl_object_hash($dbInstance2);
        $this->assertSame($a, $b);

        return $dbInstance1;
    }

    /**
     * @depends testInSingletonByProviderInterface
     *
     * @param RndDb $a
     */
    public function testInSingletonByProviderInterfaceMadeByProvider(RndDb $a)
    {
        $this->assertSame('Ray\Di\Mock\RndDbProvider::get', $a->madeBy);
    }

    public function testInSingletonByProviderClass()
    {
        $injector = Injector::create([new Modules\SingletonProviderForClassModule()]);

        $dbInstance1 = $injector->getInstance('Ray\Di\Mock\RndDb');
        $dbInstance2 = $injector->getInstance('Ray\Di\Mock\RndDb');
        $a = spl_object_hash($dbInstance1);
        $b = spl_object_hash($dbInstance2);
        $this->assertSame($a, $b);

        return $dbInstance1;
    }

    /**
     * @depends testInSingletonByProviderClass
     *
     * @param RndDb $a
     */
    public function testInSingletonByProviderClassMadeByProvider(RndDb $a)
    {
        $this->assertSame('Ray\Di\Mock\RndDbProvider::get', $a->madeBy);
    }

    public function testConsumerAskSingletonByClass()
    {
        $injector = Injector::create([new Modules\SingletonProviderForClassModule()]);
        $consumer = $injector->getInstance('Ray\Di\Mock\RndDbConsumer');

        $a = spl_object_hash($consumer->db1);
        $b = spl_object_hash($consumer->db2);
        $this->assertSame($a, $b);

        return $consumer->db1;
    }

    public function testSerializedObjectSingleton()
    {
        $injector = Injector::create([new Modules\SingletonProviderForClassModule()]);
        $instance = $injector->getInstance('Ray\Di\Mock\RndDbConsumer');
        $consumer = unserialize(serialize($instance));
        $a = spl_object_hash($consumer->db1);
        $b = spl_object_hash($consumer->db2);
        $this->assertSame($a, $b);
    }

    public function testSerializedInjectorSingleton()
    {
        $injector = unserialize(serialize(Injector::create([new Modules\SingletonProviderForClassModule()])));
        $instance = $injector->getInstance('Ray\Di\Mock\RndDbConsumer');
        $consumer = unserialize(serialize($instance));
        $a = spl_object_hash($consumer->db1);
        $b = spl_object_hash($consumer->db2);
        $this->assertSame($a, $b);
    }

    /**
     * @depends testConsumerAskSingletonByClass
     *
     * @param RndDb $a
     */
    public function testConsumerAskSingletonByClassMadeByProvider(RndDb $a)
    {
        $this->assertSame('Ray\Di\Mock\RndDbProvider::get', $a->madeBy);
    }

    public function testInSingletonInterfaceWithAnnotation()
    {
        $injector = Injector::create([new Modules\SingletonAnnotationModule()]);

        $dbInstance1 = $injector->getInstance('Ray\Di\Mock\SingletonDbInterface');
        $dbInstance2 = $injector->getInstance('Ray\Di\Mock\SingletonDbInterface');

        $a = spl_object_hash($dbInstance1);
        $b = spl_object_hash($dbInstance2);
        $this->assertSame($a, $b);
    }

    public function testInjectInSingletonInterface()
    {
        $injector = Injector::create([new Modules\SingletonModule()]);

        $numberInstance1 = $injector->getInstance('Ray\Di\Mock\Number');
        $numberInstance2 = $injector->getInstance('Ray\Di\Mock\Number');

        $a = spl_object_hash($numberInstance1->db);
        $b = spl_object_hash($numberInstance2->db);
        $this->assertSame($a, $b);
    }

    public function testInjectInSingletonInterfaceWithAnnotation()
    {
        $injector = Injector::create([new Modules\SingletonAnnotationModule()]);

        $numberInstance1 = $injector->getInstance('Ray\Di\Mock\SingletonNumber');
        $numberInstance2 = $injector->getInstance('Ray\Di\Mock\SingletonNumber');

        $a = spl_object_hash($numberInstance1->db);
        $b = spl_object_hash($numberInstance2->db);
        $this->assertSame($a, $b);
    }

    public function testInjectInSingletonInterface4times()
    {
        $injector = Injector::create([new Modules\SingletonModule()]);

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

}
