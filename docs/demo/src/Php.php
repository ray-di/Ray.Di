<?php

namespace Ray\Di\Demo;

/**
 * Third party class, not annotated.
 */
class Php implements LangInterface
{
    public $version;

    public function __construct($version = '5.5')
    {
        $this->version = $version;
    }
}