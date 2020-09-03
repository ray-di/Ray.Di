<?php

namespace Ray\Compiler;

use Ray\Di\ProviderInterface;
use Ray\Di\SetContextInterface;

class FakeContextualProvider implements ProviderInterface, SetContextInterface
{
    private $context;

    /**
     * @inheritDoc
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
