<?php

namespace Ray\Di;

use Doctrine\Common\Annotations\AnnotationReader;
use Ray\Di\Module\InjectorModule;
use Ray\Di\Di\Inject;
use Ray\Di\Di\Named;

class DiIncludedModule extends AbstractModule
{
    protected function configure()
    {
        $this->bind()->annotatedWith('greeting')->toInstance('hello');
        $this->install(new InjectorModule($this));
        $this->bind('Ray\Di\TestInterface')->toProvider('Ray\Di\TestProvider');
    }
}
class TestHasDependency
{
    public $greetingTest;

    /**
     * @param TestInterface $greetingTest
     *
     * @Inject
     */
    public function setGreetingTest(TestInterface $greetingTest)
    {
        $this->greetingTest = $greetingTest;
    }
}

interface TestInterface
{
}

class TestProvider implements ProviderInterface
{
    /**
     * @var InjectorInterface
     */
    private $injector;

    /**
     * @Inject
     */
    public function setInjector(InjectorInterface $injector)
    {
        // this injector SHOULD know the binding of @Named("greeting")
        $this->injector = $injector;
    }

    public function get()
    {
        $greetingTest =  $this->injector->getInstance('Ray\Di\GreetingTest');
        return $greetingTest;
    }
}

class GreetingTest implements TestInterface
{
    public $greeting;

    /**
     * @Inject
     * @Named("greeting")
     */
    public function setGreeting($greeting)
    {
        $this->greeting = $greeting;
    }
}


class InjectorModuleInjectorTest extends \PHPUnit_Framework_TestCase
{

    protected function setUp()
    {
        AnnotationReader::addGlobalIgnoredName('noinspection');
    }

    public function testDiIncludedModule()
    {
        $injector = Injector::create([new DiIncludedModule])->getInstance('Ray\Di\InjectorInterface');
        $greeting = $injector->getInstance('Ray\Di\GreetingTest')->greeting;
        $this->assertSame('hello', $greeting);
    }

    public function testDiIncludedModule2()
    {
        $greeting = Injector::create([new DiIncludedModule])->getInstance('Ray\Di\TestHasDependency');
        $this->assertSame('hello', $greeting->greetingTest->greeting);
    }
}
