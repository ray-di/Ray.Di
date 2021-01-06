<?php

declare(strict_types=1);

use Ray\Di\Di\Assisted;
use Ray\Di\Di\Inject;

class MovieFinder
{
    public function find($name, #[Inject] ?FinderInterface $finder = null)
    {
        return sprintf('searching [%s] by [%s]', $name, get_class($finder));
    }
}
