<?php

declare(strict_types=1);

namespace Ray\Di;

use PHPUnit\Framework\TestCase;
use Ray\Aop\MethodInvocation;
use Ray\Di\Exception\MethodInvocationNotAvailable;

use function assert;

class AssistedTest extends TestCase
{
    /** @var InjectorInterface */
    private $injector;

    protected function setUp(): void
    {
        $this->injector = new Injector(new class (new FakeToBindModule()) extends AbstractModule {
            protected function configure(): void
            {
                $this->bind(FakeAssistedConsumer::class);
            }
        });
    }

    public function testAssisted(): void
    {
        $consumer = $this->injector->getInstance(FakeAssistedConsumer::class);
        $assistedDependency = $consumer->assistOne('a', 'b');
        $expected = FakeRobot::class;
        $this->assertInstanceOf($expected, $assistedDependency);
    }

    public function testAssistedWithName(): void
    {
        $this->injector = new Injector(new FakeInstanceBindModule());
        $consumer = $this->injector->getInstance(FakeAssistedConsumer::class);
        $assistedDependency = $consumer->assistWithName('a7');
        $expected = 1;
        $this->assertSame($expected, $assistedDependency);
    }

    public function testAssistedAnyWithName(): void
    {
        $injector = new Injector(new FakeToBindModule(new FakeInstanceBindModule()));
        $consumer = $injector->getInstance(FakeAssistedConsumer::class);
        [$assistedDependency1, $assistedDependency2] = $consumer->assistAny();
        $expected1 = 1;
        $this->assertSame($expected1, $assistedDependency1);
        $this->assertInstanceOf(FakeRobot::class, $assistedDependency2);
    }

    public function testAssistedAfterOmittedDefaultArgument(): void
    {
        $consumer = $this->injector->getInstance(FakeAssistedConsumer::class);
        [$value, $assistedDependency] = $consumer->assistAfterDefault();

        $this->assertSame('default', $value);
        $this->assertInstanceOf(FakeRobot::class, $assistedDependency);
    }

    public function testAssistedPreservesExplicitNullArgument(): void
    {
        $consumer = $this->injector->getInstance(FakeAssistedConsumer::class);
        [$value, $assistedDependency] = $consumer->assistAfterDefault(null);

        $this->assertNull($value);
        $this->assertInstanceOf(FakeRobot::class, $assistedDependency);
    }

    public function testAssistedMethodInvocation(): void
    {
        $assistedConsumer = (new Injector(new FakeAssistedDbModule()))->getInstance(FakeAssistedParamsConsumer::class);
        [$id, $db] = $assistedConsumer->getUser(1);
        /** @var FakeAbstractDb $db */
        $this->assertSame(1, $id);
        $this->assertSame(1, $db->dbId);
    }

    public function testAssistedMethodInvocationNotAvailable(): void
    {
        $this->expectException(MethodInvocationNotAvailable::class);
        $assistedDbProvider = (new Injector(new FakeAssistedDbModule()))->getInstance(FakeAssistedDbProvider::class);
        $assistedDbProvider->get();
    }

    public function testAssistedMethodInvocationNotAvailableAfterCallCompletes(): void
    {
        $injector = new Injector(new FakeAssistedDbModule());
        $consumer = $injector->getInstance(FakeAssistedParamsConsumer::class);
        $consumer->getUser(1);

        $this->expectException(MethodInvocationNotAvailable::class);
        $assistedDbProvider = $injector->getInstance(FakeAssistedDbProvider::class);
        $assistedDbProvider->get();
    }

    public function testAssistedNestedInterceptionRestoresOuterInvocation(): void
    {
        $consumer = (new Injector(new FakeAssistedNestedModule()))->getInstance(FakeAssistedNestedConsumer::class);
        $invocations = $consumer->outer(1);
        assert($invocations[0] instanceof MethodInvocation);
        assert($invocations[1] instanceof MethodInvocation);

        $this->assertSame('outer', $invocations[0]->getMethod()->getName());
        $this->assertSame($invocations[0], $invocations[1]);
    }

    public function testAssistedCustomInject(): void
    {
        $assistedConsumer = (new Injector(new FakeAssistedDbModule()))->getInstance(FakeAssistedParamsConsumer::class);
        [$id] = $assistedConsumer->getUser(1);
        $this->assertSame(1, $id);
    }
}
