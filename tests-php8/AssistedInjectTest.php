<?php

declare(strict_types=1);

namespace Ray\Di;

use PHPUnit\Framework\TestCase;
use Ray\Di\Exception\MethodInvocationNotAvailable;

class AssistedInjectTest extends TestCase
{
    /** @var InjectorInterface */
    private $injector;

    protected function setUp(): void
    {
        $this->injector = new Injector(new FakeToBindModule(), __DIR__ . '/tmp');
    }

    public function testAssisted(): void
    {
        $consumer = $this->injector->getInstance(FakeAssistedInjectConsumer::class);
        /** @var FakeAssistedConsumer $consumer */
        $assistedDependency = $consumer->assistOne('a', 'b');
        $expecetd = FakeRobot::class;
        $this->assertInstanceOf($expecetd, $assistedDependency);
    }

    public function testAssistedWithName(): void
    {
        $this->injector = new Injector(new FakeInstanceBindModule());
        $consumer = $this->injector->getInstance(FakeAssistedInjectConsumer::class);
        /** @var FakeAssistedConsumer $consumer */
        $assistedDependency = $consumer->assistWithName('a7');
        $expecetd = 1;
        $this->assertSame($expecetd, $assistedDependency);
    }

    public function testAssistedAnyWithName(): void
    {
        $injector = new Injector(new FakeToBindModule(new FakeInstanceBindModule()));
        $consumer = $injector->getInstance(FakeAssistedInjectConsumer::class);
        /** @var FakeAssistedConsumer $consumer */
        [$assistedDependency1, $assistedDependency2] = $consumer->assistAny();
        $expected1 = 1;
        $this->assertSame($expected1, $assistedDependency1);
        $this->assertInstanceOf(FakeRobot::class, $assistedDependency2);
    }

    public function testAssistedMethodInvocation(): void
    {
        $assistedConsumer = (new Injector(new FakeAssistedDbModule(), __DIR__ . '/tmp'))->getInstance(FakeAssistedInjectDb::class);
        /** @var FakeAssistedParamsConsumer $assistedConsumer */
        [$id, $db] = $assistedConsumer->getUser(1);
        /** @var FakeAbstractDb $db */
        $this->assertSame(1, $id);
        $this->assertSame(1, $db->dbId);
    }

    public function testAssistedCustomeInject(): void
    {
        $injector = new Injector(new FakeInstanceBindModule());

        $assistedConsumer = $injector->getInstance(FakeAssistedInjectConsumer::class);
        /** @var FakeAssistedInjectConsumer $assistedConsumer */
        $i = $assistedConsumer->assistCustomeAssistedInject();
        $this->assertSame(1, $i);
    }

    /**
     * @requires PHP 8.1
     */
    public function testConstructorPropertyPromotion(): void
    {
        $injector = new Injector(
            new class extends AbstractModule
            {
                protected function configure()
                {
                    $this->bind()->annotatedWith('abc')->toInstance('abc');
                }
            }
        );
        $fake = $injector->getInstance(FakePropConstruct::class);
        $this->assertSame('abc', $fake->abc);
    }
}
