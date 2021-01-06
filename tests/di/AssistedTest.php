<?php

declare(strict_types=1);

namespace Ray\Di;

use PHPUnit\Framework\TestCase;
use Ray\Di\Exception\MethodInvocationNotAvailable;

class AssistedTest extends TestCase
{
    /** @var InjectorInterface */
    private $injector;

    protected function setUp(): void
    {
        $this->injector = new Injector(new FakeToBindModule(), __DIR__ . '/tmp');
    }

    public function testAssisted(): void
    {
        $consumer = $this->injector->getInstance(FakeAssistedConsumer::class);
        /** @var FakeAssistedConsumer $consumer */
        $assistedDependency = $consumer->assistOne('a', 'b');
        $expecetd = FakeRobot::class;
        $this->assertInstanceOf($expecetd, $assistedDependency);
    }

    public function testAssistedWithName(): void
    {
        $this->injector = new Injector(new FakeInstanceBindModule());
        $consumer = $this->injector->getInstance(FakeAssistedConsumer::class);
        /** @var FakeAssistedConsumer $consumer */
        $assistedDependency = $consumer->assistWithName('a7');
        $expecetd = 1;
        $this->assertSame($expecetd, $assistedDependency);
    }

    public function testAssistedAnyWithName(): void
    {
        $injector = new Injector(new FakeToBindModule(new FakeInstanceBindModule()));
        $consumer = $injector->getInstance(FakeAssistedConsumer::class);
        /** @var FakeAssistedConsumer $consumer */
        [$assistedDependency1, $assistedDependency2] = $consumer->assistAny();
        $expected1 = 1;
        $this->assertSame($expected1, $assistedDependency1);
        $this->assertInstanceOf(FakeRobot::class, $assistedDependency2);
    }

    public function testAssistedMethodInvocation(): void
    {
        $assistedConsumer = (new Injector(new FakeAssistedDbModule(), __DIR__ . '/tmp'))->getInstance(FakeAssistedParamsConsumer::class);
        /** @var FakeAssistedParamsConsumer $assistedConsumer */
        [$id, $db] = $assistedConsumer->getUser(1);
        /** @var FakeAbstractDb $db */
        $this->assertSame(1, $id);
        $this->assertSame(1, $db->dbId);
    }

    public function testAssistedMethodInvocationNotAvailable(): void
    {
        $this->expectException(MethodInvocationNotAvailable::class);
        $assistedDbProvider = (new Injector(new FakeAssistedDbModule()))->getInstance(FakeAssistedDbProvider::class);
        /** @var FakeAssistedDbProvider $assistedDbProvider */
        $assistedDbProvider->get();
    }

    public function testAssistedCustomeInject(): void
    {
        $assistedConsumer = (new Injector(new FakeAssistedDbModule(), __DIR__ . '/tmp'))->getInstance(FakeAssistedParamsConsumer::class);
        /** @var FakeAssistedParamsConsumer $assistedConsumer */
        [$id, $db] = $assistedConsumer->getUser(1);
        /** @var FakeAbstractDb $db */
        $this->assertSame(1, $id);
    }
}
