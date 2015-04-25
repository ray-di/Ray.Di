<?php

namespace Ray\Di;

class FakeLoggerPointProvider implements ProviderInterface
{
    /**
     * @var InjectionPointInterface
     */
    private $ip;

    public function __construct(InjectionPointInterface $ip)
    {
        $this->ip = $ip;
    }

    public function get()
    {
        $class = $this->ip->getClass()->getName();
        $instance = new FakeLogger($class);

        return $instance;
    }
}
