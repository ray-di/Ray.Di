<?php

declare(strict_types=1);
/**
 * This file is part of the Ray.Di package.
 *
 * @license http://opensource.org/licenses/MIT MIT
 */
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
    public $finder;

    public function __construct(FinderInterface $finder)
    {
        $this->finder = $finder;
    }
}

class FinderModule extends AbstractModule
{
    protected function configure()
    {
        $this->bind(FinderInterface::class)->to(Finder::class);
        $this->bind(MovieListerInterface::class)->to(MovieLister::class);
    }
}

$injector = new Injector(new FinderModule);
$movieLister = $injector->getInstance(MovieListerInterface::class);
/* @var $movieLister MovieLister */
$works = ($movieLister->finder instanceof Finder);

echo($works ? 'It works!' : 'It DOES NOT work!') . PHP_EOL;
