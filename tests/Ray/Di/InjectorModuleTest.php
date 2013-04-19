<?php
namespace Ray\Di;

use Doctrine\Common\Annotations\AnnotationReader;
use Ray\Di\Module\InjectorModule;
use Ray\Di\Di\Inject;
use Ray\Di\Di\Named;


class InjectorTestClass
{
    public $hash;

    /**
     * @param InjectorInterface $injector
     *
     * @\Ray\Di\Di\Inject
     */
    public function __construct(InjectorInterface $injector)
    {
        $this->hash = spl_object_hash($injector);
    }
}

class InjectorModuleTest extends \PHPUnit_Framework_TestCase
{

    protected function setUp()
    {
        AnnotationReader::addGlobalIgnoredName('noinspection');
    }

    public function testInjectorModule()
    {
        $injector = Injector::create([new InjectorModule])->getInstance('Ray\Di\InjectorInterface');
        $this->assertInstanceOf('\Ray\Di\Injector', $injector);

        return $injector;
    }

    /**
     * @depends testInjectorModule
     */
    public function testInjectorCanGetInjectorByInjectorInterface(InjectorInterface $injector)
    {
        $newInjector = $injector->getInstance('\Ray\Di\InjectorInterface');
        $this->assertInstanceOf('\Ray\Di\Injector', $newInjector);
    }

    /**
     * @depends testInjectorModule
     */
    public function testIsInjectorSingleton(InjectorInterface $injector)
    {
        $newInjector = $injector->getInstance('\Ray\Di\InjectorInterface');
        $hash1 = spl_object_hash($newInjector);
        $newInjector = $injector->getInstance('\Ray\Di\InjectorInterface');
        $hash2 = spl_object_hash($newInjector);
        $this->assertSame($hash1, $hash2);
    }
}
