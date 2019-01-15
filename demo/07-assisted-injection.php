<?php

declare(strict_types=1);

use Ray\Di\AbstractModule;
use Ray\Di\Di\Assisted;
use Ray\Di\Injector;

require dirname(__DIR__) . '/vendor/autoload.php';

interface FinderInterface
{
}

class Finder implements FinderInterface
{
}

class FinderModule extends AbstractModule
{
    protected function configure()
    {
        $this->bind(FinderInterface::class)->to(Finder::class);
    }
}

class MovieFinder
{
    /**
     * @Assisted({"finder"})
     */
    public function find($name, FinderInterface $finder = null)
    {
        return sprintf('searching [%s] by [%s]', $name, get_class($finder));
    }
}

$injector = new Injector(new FinderModule());
$finder = $injector->getInstance(MovieFinder::class);
/* @var $finder MovieFinder */
$works = $finder->find('Tokyo Story') === 'searching [Tokyo Story] by [Finder]';

echo($works ? 'It works!' : 'It DOES NOT work!') . PHP_EOL;
