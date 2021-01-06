<?php

declare(strict_types=1);

use Ray\Di\Di\Inject;

class MovieLister implements MovieListerInterface
{
    public function __construct(FinderInterface $finder)
    {
    }

    /** @Inject */
    public function setFinder01(FinderInterface $finder, FinderInterface $finder1, FinderInterface $finder2)
    {
    }
}
