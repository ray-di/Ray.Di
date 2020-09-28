<?php
namespace Ray\Di;

class FakeClassInstanceBindModule extends AbstractModule
{
    private $object;

    public function __construct($object)
    {
        $this->object = $object;
        parent::__construct();
    }

    protected function configure()
    {
        $this->bind(FakeEngine::class)->toInstance($this->object);
    }
}
