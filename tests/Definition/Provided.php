<?php

namespace Aura\Di\Definition;

use Aura\Di\Mock\ReaderInterface;

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