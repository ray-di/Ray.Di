<?php
namespace Ray\Di;

class FakeContextualProvider implements ProviderInterface, SetContextInterface
{
    private $context;

    /**
     * {@inheritdoc}
     */
    public function setContext($context)
    {
        $this->context = $context;
    }

    public function get()
    {
        return new FakeContextualRobot($this->context);
    }
}
