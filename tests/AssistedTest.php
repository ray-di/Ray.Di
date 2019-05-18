<?php

declare(strict_types=1);

namespace Ray\Di;

use PHPUnit\Framework\TestCase;
use Ray\Di\Exception\MethodInvocationNotAvailable;

class AssistedTest extends TestCase
{
    /**
     * @var InjectorInterface
     */
    private $injector;

    protected function setUp() : void
    {
        $this->injector = new Injector(new FakeToBindModule, $_ENV['TMP_DIR']);
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
        $assistedConsumer = (new Injector(new FakeAssistedDbModule, $_ENV['TMP_DIR']))->getInstance(FakeAssistedParamsConsumer::class);
        /* @var $assistedConsumer FakeAssistedParamsConsumer */
        list($id, $db) = $assistedConsumer->getUser(1);
        /* @var $db FakeAbstractDb */
        $this->assertSame(1, $id);
        $this->assertSame(1, $db->dbId);
    }

    public function testAssistedMethodInvocationNotAvailable()
    {
        $this->expectException(MethodInvocationNotAvailable::class);
        $assistedDbProvider = (new Injector(new FakeAssistedDbModule))->getInstance(FakeAssistedDbProvider::class);
        /* @var $assistedDbProvider FakeAssistedDbProvider */
        $assistedDbProvider->get();
    }
}
