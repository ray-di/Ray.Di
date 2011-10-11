<?php

namespace Aura\Di\Definition;

use Aura\Di\Mock\logInterface,
    Aura\Di\Mock\UserInterface;

class Implemented
{
    /**
     * @var LogInterface
     */
    public $log;

    /**
     * @Inject
     *
     * @param LogInterface $log
     */
    public function setLog(LogInterface $log)
    {
        $this->log = $log;
    }
}