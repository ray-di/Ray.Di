<?php

use Ray\Di\FakeAbstractDb;

namespace Ray\Di;

use Ray\Compiler\DiCompiler;
use Ray\Di\Exception\Unbound;
use Ray\Di\Exception\Untargetted;

class AssistedTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var InjectorInterface
     */
    private $injector;

    public function setup()
    {
        $this->injector = new Injector(new FakeToBindModule);
    }

    public function tearDown()
    {
        parent::tearDown();
        foreach (new \RecursiveDirectoryIterator($_ENV['TMP_DIR'], \FilesystemIterator::SKIP_DOTS) as $file) {
            unlink($file);
        }
    }

    public function testAssisted()
    {
        $consumer = $this->injector->getInstance(FakeAssistedConsumer::class);
        /* @var $consumer FakeAssistedConsumer */
        $assistedDependency = $consumer->assistOne('a', 'b');
        $expecetd = FakeRobot::class;
        $this->assertInstanceOf($expecetd, $assistedDependency);
    }

    public function testAssistedWithName()
    {
        $this->injector = new Injector(new FakeInstanceBindModule);
        $consumer = $this->injector->getInstance(FakeAssistedConsumer::class);
        /* @var $consumer FakeAssistedConsumer */
        $assistedDependency = $consumer->assistWithName('a7');
        $expecetd = 1;
        $this->assertSame($expecetd, $assistedDependency);
    }

    public function testAssistedAnyWithName()
    {
        $injector = new Injector(new FakeToBindModule(new FakeInstanceBindModule));
        $consumer = $injector->getInstance(FakeAssistedConsumer::class);
        /* @var $consumer FakeAssistedConsumer */
        list($assistedDependency1, $assistedDependency2) = $consumer->assistAny();
        $expected1 = 1;
        $this->assertSame($expected1, $assistedDependency1);
        $this->assertInstanceOf(FakeRobot::class, $assistedDependency2);
    }

    public function testAssistedMethodInvocation()
    {
        $assistedConsumer = (new Injector(new FakeAssistedDbModule))->getInstance(FakeAssistedParamsConsumer::class);
        /* @var $assistedConsumer FakeAssistedParamsConsumer */
        list($id, $db) = $assistedConsumer->getUser(1);
        /* @var $db FakeAbstractDb */
        $this->assertSame(1, $id);
        $this->assertSame(1, $db->dbId);
    }
}
