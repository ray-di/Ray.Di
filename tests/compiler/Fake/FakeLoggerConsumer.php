<?php

namespace Ray\Compiler;

use Ray\Di\Di\Inject;
use Ray\Di\InjectorInterface;

class FakeLoggerConsumer
{
    /**
     * @var FakeLoggerInterface
     */
    public $logger;

    public $injector;


    public function __construct(InjectorInterface $injector)
    {
        $this->injector = $injector;
    }

    /**
     * @FakeLoggerInject(type="MEMORY")
     */
    public function setLogger(FakeLoggerInterface $logger)
    {
        $this->logger = $logger;
    }
}
