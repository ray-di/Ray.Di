<?php

declare(strict_types=1);

use Ray\Di\AbstractModule;
use Ray\Di\Injector;

require dirname(__DIR__) . '/vendor/autoload.php';

interface FinderInterface
{
}

class Finder implements FinderInterface
{
}

interface MovieListerInterface
{
}

class MovieLister implements MovieListerInterface
{
    public function __construct(
        public FinderInterface $finder
    ){}
}

class FinderModule extends AbstractModule
{
    protected function configure()
    {
        $this->bind(FinderInterface::class)->to(Finder::class);
        $this->bind(MovieListerInterface::class)->to(MovieLister::class);
    }
}

$injector = new Injector(new FinderModule());
$movieLister = $injector->getInstance(MovieListerInterface::class);
/** @var MovieLister $movieLister */
$works = ($movieLister->finder instanceof Finder);

echo($works ? 'It works!' : 'It DOES NOT work!') . PHP_EOL;
