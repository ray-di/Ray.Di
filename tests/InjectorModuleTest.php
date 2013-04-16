<?php
namespace Ray\Di;

use Doctrine\Common\Annotations\AnnotationReader;
use Ray\Di\Module\DiModule;

class InjectorModuleTest extends \PHPUnit_Framework_TestCase
{

    protected function setUp()
    {
        AnnotationReader::addGlobalIgnoredName('noinspection');
    }

    public function testDiModule()
    {
        $injector = Injector::create([new DiModule])->getInstance('Ray\Di\InjectorInterface');
        $this->assertInstanceOf('Ray\Di\Injector', $injector);
    }

    public function testInjectorModule()
    {
        $injector = Injector::create([new Modules\InjectorModule])->getInstance('Ray\Di\InjectorInterface');
        $this->assertInstanceOf('Ray\Di\Injector', $injector);
    }

}
