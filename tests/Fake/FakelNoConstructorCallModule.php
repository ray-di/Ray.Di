<?php

namespace Ray\Di;

class FakelNoConstructorCallModule extends AbstractModule
{
    public function __construct(AbstractModule $module = null)
    {
    }

    public function configure()
    {
    }
}
