<?php

declare(strict_types=1);

namespace Ray\Di;

use Ray\Di\Annotation\FakeInjectOne;

class FakeBcParameterQualifierModule extends AbstractModule
{
    protected function configure(): void
    {
        $this->bind(FakeClassWithBcParameterQualifier::class);
        // Bind with FakeInjectOne qualifier
        $this->bind(FakeGearStickInterface::class)
            ->annotatedWith(FakeInjectOne::class)
            ->to(FakeLeatherGearStick::class);
    }
}
