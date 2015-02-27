<?php

namespace Ray\Di\Demo;

class Computer implements ComputerInterface
{
    public $lang;

    public function __construct(LangInterface $lang)
    {
        $this->lang = $lang;
    }
}
