<?php

declare(strict_types=1);

namespace Ray\Di;

use Ray\Di\Annotation\FakeLeft;
use Ray\Di\Annotation\FakeRight;

class FakePhp8CarModule extends AbstractModule
{
    protected function configure()
    {
        $this->bind(FakeCarInterface::class)->to(FakePhp8Car::class); // dependent
        $this->bind(FakeEngineInterface::class)->to(FakeEngine::class); // constructor
        $this->bind(FakeTyreInterface::class)->to(FakeTyre::class); // setter
        $this->bind(FakeMirrorInterface::class)->annotatedWith('right')->to(FakeMirrorRight::class)->in(Scope::SINGLETON); // named binding
        $this->bind(FakeMirrorInterface::class)->annotatedWith('left')->to(FakeMirrorLeft::class)->in(Scope::SINGLETON); // named binding
        $this->bind(FakeMirrorInterface::class)->annotatedWith(FakeLeft::class)->to(FakeMirrorLeft::class); // named binding
        $this->bind(FakeMirrorInterface::class)->annotatedWith(FakeRight::class)->to(FakeMirrorRight::class); // named binding
        $this->bind('')->annotatedWith('logo')->toInstance('momo');
        $this->bind(FakeHandleInterface::class)->toProvider(FakeHandleProvider::class);
        $this->bind(FakeGearStickInterface::class)->toProvider(FakeGearStickProvider::class);
    }
}
