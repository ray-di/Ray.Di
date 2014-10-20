<?php

namespace Ray\Di;

class FakeExplicitCarModule extends AbstractModule
{
    protected function configure()
    {
        $this->bind(FakeCarInterface::class)->toExplicit(
            FakeCar::class,
            (new InjectionPoints)->addMethod('setTires')->addMethod('setHardtop'),
            'postConstruct'
        );
        $this->bind(FakeEngineInterface::class)->to(FakeEngine::class);
        $this->bind(FakeHardtopInterface::class)->to(FakeHardtop::class);
        $this->bind(FakeTyreInterface::class)->to(FakeTyre::class);
    }
}
