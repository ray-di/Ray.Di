<?php

namespace Ray\Di\Demo;

require dirname(__DIR__) . '/vendor/autoload.php';

use Ray\Di\Di\Inject;
use Ray\Di\AbstractModule;

interface FinderInterface {}

interface MovieListerInterface {}

class Finder implements FinderInterface {}


class MovieLister implements MovieListerInterface
{
    public $finder;

    /**
     * @Inject
     */
    public function __construct(FinderInterface $finder)
    {
        $this->finder = $finder;
    }
}

class ListerModule extends AbstractModule
{
    protected function configure()
    {
        $this->bind(FinderInterface::class)->to(Finder::class);
        $this->bind(MovieListerInterface::class)->to(MovieLister::class);
    }
}

