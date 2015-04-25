<?php

namespace Ray\Di;

class FakeLoggerConsumer
{
    /**
     * @var FakeLoggerInterface
     */
    public $logger;

    public function __construct(FakeLoggerInterface $logger)
    {
        $this->logger = $logger;
    }
}
