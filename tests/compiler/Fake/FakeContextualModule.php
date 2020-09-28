<?php

namespace Ray\Compiler;

use Ray\Di\AbstractModule;

class FakeContextualModule extends AbstractModule
{
    private $context;

    public function __construct($context)
    {
        $this->context = $context;
    }

    protected function configure()
    {
        $this->bind(FakeRobotInterface::class)->toProvider(FakeContextualProvider::class, $this->context);
    }
}
