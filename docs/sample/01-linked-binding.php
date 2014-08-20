<?php

namespace Ray\Di\Sample;

use Ray\Di\Di\Inject;
use Ray\Di\Di\Named;
use Ray\Di\AbstractModule;
use Ray\Di\Injector;

$loader = require dirname(dirname(__DIR__)) . '/vendor/autoload.php';

interface MovieFinderInterface
{
}

class MovieFinder implements MovieFinderInterface
{
}

interface MovieListerInterface
{
}

class MovieLister implements MovieListerInterface
{
    /**
     * @var MovieFinderInterface
     */
    public $finder;

    /**
     * @Inject
     */
    public function __construct(MovieFinderInterface $finder)
    {
        $this->finder = $finder;
    }
}

class MovieListerModule extends AbstractModule
{
    protected function configure()
    {
        $this->bind('Ray\Di\Sample\MovieFinderInterface')->to('Ray\Di\Sample\MovieFinder');
        $this->bind('Ray\Di\Sample\MovieListerInterface')->to('Ray\Di\Sample\MovieLister');
    }
}

$injector = Injector::create([new MovieListerModule]);
$movieLister = $injector->getInstance('Ray\Di\Sample\MovieListerInterface');
/** @var $movieLister \Ray\Di\Sample\MovieListerInterface */

$works = ($movieLister->finder instanceof MovieFinder);
echo (($works) ? 'It works!' : 'It DOES NOT work!') . PHP_EOL;
