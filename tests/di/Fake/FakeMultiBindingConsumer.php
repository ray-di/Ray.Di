<?php

declare(strict_types=1);

namespace Ray\Di;


use Ray\Di\Di\Set;
use Ray\Di\MultiBinding\Map;

final class FakeMultiBindingConsumer
{
    /**
     * @param Map<FakeEngineInterface> $engines
     * @param Map<FakeRobotInterface> $robots
     */
    public function __construct(
        #[Set(FakeEngineInterface::class)] public Map $engines,
        #[Set(FakeRobotInterface::class)] public Map $robots
    ){}

    public function testValid(): void
    {
        $f = $this->engines['one'];
        // cause no error in psalm
        $f->foo();
    }

    /**
     * Test generic
     *
     * This tests generic expression of Map<FakeEngineInterface>
     * Not called from any place. Created to confirm "@psalm-suppress" works with psalm
     *
     * @psalm-suppress UndefinedInterfaceMethod
     */
    public function testInvalid(): void
    {
        $f = $this->engines['one'];
        // cause error in psalm
        $f->warnUndefinedInterfaceMethod();
    }
}
