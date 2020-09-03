<?php
namespace Ray\Di;

class FakeContextualModule extends AbstractModule
{
    private $context;

    public function __construct($context)
    {
        $this->context = $context;
        parent::__construct();
    }

    protected function configure()
    {
        $this->bind(FakeRobotInterface::class)->toProvider(FakeContextualProvider::class, $this->context);
    }
}
