<?php

declare(strict_types=1);

namespace Ray\Compiler;

use Ray\Di\InjectorInterface;

class FakeLoggerConsumer
{
    /** @var FakeLoggerInterface */
    public $logger;

    /** @var InjectorInterface */
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
