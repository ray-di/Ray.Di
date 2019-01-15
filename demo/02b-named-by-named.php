<?php

declare(strict_types=1);

use Ray\Di\AbstractModule;
use Ray\Di\Di\Named;
use Ray\Di\Injector;

require dirname(__DIR__) . '/vendor/autoload.php';

interface FinderInterface
{
}

class LegacyFinder implements FinderInterface
{
}

class ModernFinder implements FinderInterface
{
}

interface MovieListerInterface
{
}

class MovieLister implements MovieListerInterface
{
    public $finder;

    /**
     * @Named("legacy")
     */
    public function __construct(FinderInterface $finder)
    {
        $this->finder = $finder;
    }
}

class FinderModule extends AbstractModule
{
    protected function configure()
    {
        $this->bind(FinderInterface::class)->annotatedWith('legacy')->to(LegacyFinder::class);
        $this->bind(MovieListerInterface::class)->to(MovieLister::class);
    }
}

$injector = new Injector(new FinderModule);
$movieLister = $injector->getInstance(MovieListerInterface::class);
/* @var $movieLister MovieLister */
$works = ($movieLister->finder instanceof LegacyFinder);

echo($works ? 'It works!' : 'It DOES NOT work!') . PHP_EOL;
