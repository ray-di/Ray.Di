<?php

namespace Ray\Di\Definition;

use Ray\Di\Mock\ReaderInterface;

class Provided
{
    /**
     * @var ReaderInterface
     */
    public $reader;

    /**
     * @Inject
     *
     * @param ReaderInterface $log
     */
    public function setReader(ReaderInterface $reader)
    {
        $this->reader = $reader;
    }
}