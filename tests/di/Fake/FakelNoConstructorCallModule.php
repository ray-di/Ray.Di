<?php

declare(strict_types=1);

namespace Ray\Di;

class FakelNoConstructorCallModule extends AbstractModule
{
    public function __construct()
    {
    }

    public function configure()
    {
    }
}
