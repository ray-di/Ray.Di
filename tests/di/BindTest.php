<?php

declare(strict_types=1);

namespace Ray\Di;

use PHPUnit\Framework\TestCase;
use Ray\Di\Exception\InvalidProvider;
use Ray\Di\Exception\InvalidType;
use Ray\Di\Exception\NotFound;

class BindTest extends TestCase
{
    /**
     * @var Bind
     */
    private $bind;

    protected function setUp() : void
    {
        parent::setUp();
        $this->bind = new Bind(new Container, FakeTyreInterface::class);
    }

    public function testGetBound() : void
    {
        $this->bind->to(FakeTyre::class);
        $bound = $this->bind->getBound();
        $this->assertInstanceOf(Dependency::class, $bound);
    }

    public function testToString() : void
    {
        $this->assertSame('Ray\Di\FakeTyreInterface-' . Name::ANY, (string) $this->bind);
    }

    public function testInvalidToTest() : void
    {
        $this->expectException(Notfound::class);
        $this->bind->to('invalid-class');
    }

    public function testInvalidToProviderTest() : void
    {
        $this->expectException(Notfound::class);
        $this->bind->toProvider('invalid-class'); // @phpstan-ignore-line
    }

    public function testInValidInterfaceBinding() : void
    {
        $this->expectException(NotFound::class);
        new Bind(new Container, 'invalid-interface');
    }

    public function testUntargetedBind() : void
    {
        $container = new Container;
        $bind = new Bind($container, FakeEngine::class);
        unset($bind);
        $container = $container->getContainer();
        $this->assertArrayHasKey(FakeEngine::class . '-' . Name::ANY, $container);
    }

    public function testUntargetedBindSingleton() : void
    {
        $container = new Container;
        $bind = (new Bind($container, FakeEngine::class))->in(Scope::SINGLETON);
        unset($bind);
        $dependency1 = $container->getInstance(FakeEngine::class, Name::ANY);
        $dependency2 = $container->getInstance(FakeEngine::class, Name::ANY);
        $this->assertSame(spl_object_hash($dependency1), spl_object_hash($dependency2));
    }

    /**
     * @return (string|string[])[][]
     *
     * @psalm-return array{0: array{0: string}, 1: array{0: array{tmpDir: string, leg: string}}}
     */
    public function nameProvider() : array
    {
        return [
            ['tmpDir=tmp_dir,leg=left'],
            [['tmpDir' => 'tmp_dir', 'leg' => 'left']]
        ];
    }

    /**
     * @dataProvider nameProvider
     *
     * @param array<string, string>|string $name
     */
    public function testToConstructor($name) : void
    {
        $container = new Container;
        $container->add((new Bind($container, ''))->annotatedWith('tmp_dir')->toInstance('/tmp'));
        $container->add((new Bind($container, FakeLegInterface::class))->annotatedWith('left')->to(FakeLeftLeg::class));
        $container->add((new Bind($container, FakeRobotInterface::class))->toConstructor(FakeToConstructorRobot::class, $name));
        $instance = $container->getInstance(FakeRobotInterface::class, Name::ANY);
        /* @var $instance FakeToConstructorRobot */
        $this->assertInstanceOf(FakeLeftLeg::class, $instance->leg);
        $this->assertSame('/tmp', $instance->tmpDir);
    }

    public function testToConstructorWithMethodInjection() : void
    {
        $container = new Container;
        $container->add((new Bind($container, ''))->annotatedWith('tmp_dir')->toInstance('/tmp'));
        $container->add((new Bind($container, FakeLegInterface::class))->annotatedWith('left')->to(FakeLeftLeg::class));
        $container->add((new Bind($container, FakeEngineInterface::class))->to(FakeEngine::class));
        $container->add(
            (new Bind($container, FakeRobotInterface::class))->toConstructor(
                FakeToConstructorRobot::class,
                'tmpDir=tmp_dir,leg=left',
                (new InjectionPoints)->addMethod('setEngine')
            )
        );
        $instance = $container->getInstance(FakeRobotInterface::class, Name::ANY);
        /* @var $instance FakeToConstructorRobot */
        $this->assertInstanceOf(FakeEngine::class, $instance->engine);
    }

    public function testToValidation() : void
    {
        $this->expectException(InvalidType::class);
        (new Bind(new Container, FakeHandleInterface::class))->to(FakeEngine::class);
    }

    public function testToProvider() : void
    {
        $this->expectException(InvalidProvider::class);
        (new Bind(new Container, FakeHandleInterface::class))->toProvider(FakeEngine::class);
    }

    public function testBindProviderAsProvider() : void
    {
        $container = new Container;
        (new Bind($container, ProviderInterface::class))->annotatedWith('handle')->to(FakeHandleProvider::class);
        $instance = $container->getInstance(ProviderInterface::class, 'handle');
        $this->assertInstanceOf(FakeHandleProvider::class, $instance);
    }

    public function testBindProviderAsProviderInSingleton() : void
    {
        $container = new Container;
        (new Bind($container, ProviderInterface::class))->annotatedWith('handle')->to(FakeHandleProvider::class)->in(Scope::SINGLETON);
        $instance1 = $container->getInstance(ProviderInterface::class, 'handle');
        $instance2 = $container->getInstance(ProviderInterface::class, 'handle');
        $this->assertSame(spl_object_hash($instance1), spl_object_hash($instance2));
    }

    public function testProviderContext() : void
    {
        $container = new Container;
        $bind = (new Bind($container, ProviderInterface::class))->toProvider(FakeContextualProvider::class, 'context_string');
        $instance = $container->getInstance(ProviderInterface::class, Name::ANY);
        $this->assertSame('context_string', $instance->context);
    }
}
