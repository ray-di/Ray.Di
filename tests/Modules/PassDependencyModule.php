<?php

namespace Ray\Di\Modules;

use Ray\Di\AbstractModule;

class PassDependencyModule extends AbstractModule
{
    /**
     * @var int
     */
    private $a;

    /**
     * @param int $a
     */
    public function __construct($a)
    {
        $this->a = $a;
        parent::__construct();
    }

    protected function configure()
    {
        $this->bind()->annotatedWith("val_a")->toInstance($this->a);
    }
}
