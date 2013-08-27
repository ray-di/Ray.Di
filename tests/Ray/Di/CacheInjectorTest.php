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

    /**
     * Sets up the fixture, for example, opens a network connection.
     * This method is called before a test is executed.
     */
    protected function setUp()
    {
        parent::setUp();
        $this->injector = new CacheInjector(null, null, new FilesystemCache($_ENV['RAY_TMP']));
    }

    public function testNew()
    {
        $mock = $this->injector->getInstance('Ray\Di\Definition\LifeCycle');
        $this->assertSame('@PostConstruct', $mock->msg);
    }

    public function testToClass()
    {
        $injector = new CacheInjector(
            function () {
                return new Modules\BasicModule;
            },
            $_ENV['RAY_TMP']
        );
        $instance = $injector->getInstance('Ray\Di\Definition\Basic');
        $this->assertInstanceOf('\Ray\Di\Mock\UserDb', $instance->db);

        return $injector;
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
        $injector = new CacheInjector(
            function () {
                return new Modules\InstanceModule;
            },
            $_ENV['RAY_TMP']
        );
        $instance = $injector->getInstance('Ray\Di\Definition\Instance');
        $this->assertSame('PC6001', $instance->userId);
    }

    public function testAop()
    {
        $injector = new CacheInjector(
            function () {
                return new Modules\AopModule;
            },
            $_ENV['RAY_TMP']
        );
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

}
