<?php

declare(strict_types=1);

use Composer\Autoload\ClassLoader;
use Ray\Di\AbstractModule;
use Ray\Di\Injector;

$loader = require dirname(__DIR__) . '/vendor/autoload.php';
/** @var ClassLoader $loader */
$loader->addPsr4('', __DIR__ . '/finder');

$injector = new Injector(new class extends AbstractModule{
    protected function configure()
    {
        $this->bind(FinderInterface::class)->to(Finder::class);
    }
});
$finder = $injector->getInstance(MovieFinder::class);
/** @var MovieFinder $finder */
$works = $finder->find('Tokyo Story') === 'searching [Tokyo Story] by [Finder]';

echo($works ? 'It works!' : 'It DOES NOT work!') . PHP_EOL;
