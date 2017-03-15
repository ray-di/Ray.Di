<?php
namespace Ray\Di;

class FakeOpenCarModule extends AbstractModule
{
    protected function configure()
    {
        $this->bind(FakeCarInterface::class)->to(FakeCar::class);
        $this->bind(FakeEngineInterface::class)->to(FakeEngine::class);
        // No hardtop, Go open !
        // $this->bind(FakeHardtopInterface::class)->to(FakeHardtop::class);
        $this->bind(FakeTyreInterface::class)->to(FakeTyre::class);
    }
}
