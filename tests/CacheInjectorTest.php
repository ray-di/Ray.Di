<?php

namespace Ray\Di;

use Doctrine\Common\Annotations\AnnotationReader;
use Doctrine\Common\Cache\FilesystemCache;
use Doctrine\Common\Cache\ArrayCache;
use Ray\Aop\Bind;
use Ray\Aop\Compiler;
use PHPParser_PrettyPrinter_Default;

class CacheInjectorTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var CacheInjector
     */
    protected $injector;

    /**
     * @var bool
     */
    private $flag = false;

    protected function setUp()
    {
        parent::setUp();
        $this->injector = function () {return new Injector(new Container(new Forge(new Config(new Annotation(new Definition, new AnnotationReader)))), new Modules\BasicModule, new Bind, new Compiler($_ENV['TMP_DIR'], new PHPParser_PrettyPrinter_Default));};
        $initialization = function() { $this->flag = true; };
        $this->injector = new CacheInjector($this->injector, $initialization, 'test', new FilesystemCache($_ENV['TMP_DIR']));
    }

    public function testNew()
    {
        $mock = $this->injector->getInstance('Ray\Di\Definition\LifeCycle');
        $this->assertSame('@PostConstruct', $mock->msg);
    }

    public function testToClass()
    {
        $instance = $this->injector->getInstance('Ray\Di\Definition\Basic');
        $this->assertInstanceOf('\Ray\Di\Mock\UserDb', $instance->db);
        return $this->injector;
    }

    /**
     * @param CacheInjector $injector
     * @depends testToClass
     */
    public function testToClass2nd(CacheInjector $injector)
    {
        $instance = $injector->getInstance('Ray\Di\Definition\Basic');
        $this->assertInstanceOf('\Ray\Di\Mock\UserDb', $instance->db);
    }

    public function testToInstance()
    {
        $injector = function () {return Injector::create([new Modules\InstanceModule]);};
        $initialization = function() {};
        $injector = new CacheInjector($injector, $initialization, 'test', new FilesystemCache($_ENV['TMP_DIR']));

        $instance = $injector->getInstance('Ray\Di\Definition\Instance');
        $this->assertSame('PC6001', $instance->userId);
    }

    public function testAop()
    {
        $injector = function () {return Injector::create([new Modules\AopModule]);};
        $initialization = function() {};
        $injector = new CacheInjector($injector, $initialization, 'test', new FilesystemCache($_ENV['TMP_DIR']));
        $instance = $injector->getInstance('Ray\Di\Aop\RealBillingService');
        /* @var $instance \Ray\Di\Aop\RealBillingService */
        list($amount, ) = $instance->chargeOrder();
        $expected = 105;
        $this->assertSame($expected, (int) $amount);

        return $injector;
    }

    /**
     * @param $injector
     *
     * @depends testAop
     */
    public function testAop2nd(CacheInjector $injector)
    {
        $instance = $injector->getInstance('Ray\Di\Aop\RealBillingService');
        /* @var $instance \Ray\Di\Aop\RealBillingService */
        list($amount, ) = $instance->chargeOrder();
        $expected = 105;
        $this->assertSame($expected, (int) $amount);
    }

    public function testPostInject()
    {
        $flag = false;
        $injector = function () {return Injector::create([new Modules\AopModule]);};
        $initialization = function($instance) use (&$flag){ $flag = true;};
        $injector = new CacheInjector($injector, $initialization, 'test', new ArrayCache);
        $injector->getInstance('Ray\Di\Aop\RealBillingService');
        $this->assertTrue($flag);
    }

    public function testCachedAopInject()
    {
        require __DIR__ . '/scripts/cache_billing.php';
        $serialized = require __DIR__ . '/scripts/cache_billing.php';
        $instance = unserialize($serialized);

        $this->assertInstanceOf('Ray\Aop\WeavedInterface', $instance);
        return $instance;
    }

    public function testCachedAopInjectClassAopFilesAreDeleted()
    {
        $serialized = require __DIR__ . '/scripts/cache_billing.php';
        foreach (glob(__DIR__ . '/scripts/aop_files/*.php') as $file) {
            unlink($file);
        }
        $instance = unserialize($serialized);
        $this->assertInstanceOf('Ray\Aop\WeavedInterface', $instance);
        $this->assertInstanceOf('Ray\Aop\WeavedInterface', unserialize(require __DIR__ . '/scripts/cache_billing.php'));
        return $instance;
    }

    /**
     * @param $instance
     *
     * @depends testCachedAopInject
     */
    public function testCachedObjectParent($instance)
    {
        $this->assertTrue('Ray\Di\Aop\CacheBilling' === get_parent_class($instance));
    }

    public function testLifeCycle()
    {
        $this->injector->getInstance('Ray\Di\Definition\LifeCycleOnShutdown');
        register_shutdown_function(
            function () {
                $this->assertSame('@PreDestroy', $GLOBALS['PreDestroy_on_shutdown']);
            }
        );
    }

    /**
     * @expectedException \Ray\Di\Exception\NoInjectorReturn
     */
    public function testInjectorNotReturned()
    {
        $injector = function () {return null;};
        $initialization = function($instance) use (&$flag){ $flag = true;};
        $injector = new CacheInjector($injector, $initialization, __FUNCTION__, new ArrayCache);
        $injector->getInstance('Ray\Di\Definition\LifeCycleOnShutdown');
    }
}
