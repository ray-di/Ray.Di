<?php

namespace Ray\Di\Mock\MovieApp;

class Lister
{
    /**
     * @var Finder
     */
    public $finder;

    public function __construct(Finder $finder)
    {
        $this->finder = $finder;
    }
}

class Finder
{
}
