<?php

declare(strict_types=1);

use Ray\Di\Di\Assisted;

class MovieFinder
{
    /**
     * @Assisted({"finder"})
     */
    public function find($name, ?FinderInterface $finder = null)
    {
        return sprintf('searching [%s] by [%s]', $name, get_class($finder));
    }
}
