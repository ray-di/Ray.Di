<?php
namespace Ray\Di;

use Ray\Di\Di\Named;

class FakeInjectionPoint implements ProviderInterface
{
    public $ip;

    /**
     * @Named("aa")
     */
    public function __construct(\ReflectionParameter $ip)
    {
        $this->ip = $ip;
    }

    public function get()
    {
        if ($this->ip->getName()) {
            return $this->ip;
        }
    }
}
