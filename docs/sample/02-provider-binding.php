<?php

namespace Ray\Di\Sample;

use MovieApp\Finder;
use Ray\Di\Di\Inject;
use Ray\Di\Di\Named;
use Ray\Di\AbstractModule;
use Ray\Di\Injector;
use Ray\Di\ProviderInterface;

$loader = require dirname(dirname(__DIR__)) . '/vendor/autoload.php';

interface MovieFinderInterface
{
}

class MovieFinder implements MovieFinderInterface
{
    public function __construct($movieFile)
    {
    }
}

interface MovieListerInterface
{
}

class MovieFinderProvider implements ProviderInterface
{
    private $movieFile;

    /**
     * @param $movieFile
     *
     * @Inject
     * @Named("movie_file_path")
     */
    public function __construct($movieFile)
    {
        $this->movieFile = $movieFile;
    }

    public function get()
    {
        $finder = new MovieFinder($this->movieFile);

        return $finder;
    }
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
        $this->bind('')->annotatedWith('movie_file_path')->toInstance(__DIR__ . '/MovieFile.csv');
        $this->bind('Ray\Di\Sample\MovieFinderInterface')->toProvider('Ray\Di\Sample\MovieFinderProvider');
        $this->bind('Ray\Di\Sample\MovieListerInterface')->to('Ray\Di\Sample\MovieLister');
    }
}

$injector = Injector::create([new MovieListerModule]);
$movieLister = $injector->getInstance('Ray\Di\Sample\MovieListerInterface');
/** @var $movieLister \Ray\Di\Sample\MovieListerInterface */

$works = ($movieLister->finder instanceof MovieFinderInterface);
echo (($works) ? 'It works!' : 'It DOES NOT work!') . PHP_EOL;
