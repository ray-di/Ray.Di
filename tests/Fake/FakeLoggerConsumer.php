<?php

namespace Ray\Di;

class FakeLoggerConsumer
{
    /**
     * @var FakeLoggerInterface
     */
    public $logger;

    public $injector;

    public function __construct(FakeLoggerInterface $logger, InjectorInterface $injector)
    {
        $this->logger = $logger;
        $this->injector = $injector;
    }
}
