<?php
namespace Ray\Di;

class FakelNoConstructorCallModule extends AbstractModule
{
    public function __construct(AbstractModule $module = null)
    {
        parent::__construct($module);
    }

    public function configure()
    {
    }
}
