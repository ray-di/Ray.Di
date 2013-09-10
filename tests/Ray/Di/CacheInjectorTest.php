<?php
namespace Ray\Di;

use Doctrine\Common\Annotations\AnnotationReader;
use Doctrine\Common\Cache\PhpFileCache;
use Doctrine\Common\Cache\FilesystemCache;
use Doctrine\Common\Cache\ArrayCache;
use Ray\Di\Modules\InstanceInstallModule;
use Ray\Di\Modules\InstanceModule;

class CacheInjectorTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var Injector
     */
    protected $injector;

    protected $config;
    protected $container;

    private $flag = false;

    protected function setUp()
    {
        parent::setUp();
        $injector = function () {return Injector::create([new Modules\BasicModule]);};
        $postInject = function() { $this->flag = true; };
        $this->injector = new CacheInjector($injector, $postInject, 'test', new ArrayCache, sys_get_temp_dir());
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
     * @param InjectorInterface $injector
     *
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
        $postInject = function() {};
        $injector = new CacheInjector($injector, $postInject, 'test', new ArrayCache, $_ENV['RAY_TMP']);

        $instance = $injector->getInstance('Ray\Di\Definition\Instance');
        $this->assertSame('PC6001', $instance->userId);
    }

    public function testAop()
    {
        $injector = function () {return Injector::create([new Modules\AopModule]);};
        $postInject = function() {};
        $injector = new CacheInjector($injector, $postInject, 'test', new ArrayCache, $_ENV['RAY_TMP']);
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
        $postInject = function($instance) use (&$flag){ $flag = true;};
        $injector = new CacheInjector($injector, $postInject, 'test');
        $injector->getInstance('Ray\Di\Aop\RealBillingService');
        $this->assertTrue($flag);
    }

    public function testCachedAopInject()
    {
        $this->assertFalse(class_exists('Ray\Di\Aop\CacheBilling', false));

        $cli = 'php ' . __DIR__ . '/scripts/cache.php';
        passthru($cli, $return);
        $this->assertFalse(class_exists('Ray\Di\Aop\CacheBilling', false));

        $this->assertFalse(class_exists('Ray\Di\Aop\CacheBilling', false));
        $cli = 'php ' . __DIR__ . '/scripts/cache.php';
        passthru($cli, $return);

        $serialized = require __DIR__ . '/scripts/cache.php';
        $instance = unserialize($serialized);

        $this->assertInstanceOf('Ray\Aop\WeavedInterface', $instance);
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

}
