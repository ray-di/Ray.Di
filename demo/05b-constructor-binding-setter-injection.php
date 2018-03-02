<?php

declare(strict_types=1);
/**
 * This file is part of the Ray.Di package.
 *
 * @license http://opensource.org/licenses/MIT MIT
 */
namespace Ray\Di\Demo;

use Ray\Di\AbstractModule;
use Ray\Di\InjectionPoints;
use Ray\Di\Injector;

require __DIR__ . '/bootstrap.php';

interface FinderInterface
{
}
interface MovieListerInterface
{
}

class Finder implements FinderInterface
{
}

class MovieLister implements MovieListerInterface
{
    public $finder;

    /**
     * Setter Injection
     */
    public function setFinder(FinderInterface $finder)
    {
        $this->finder = $finder;
    }
}

class Lister2Module extends AbstractModule
{
    protected function configure()
    {
        $this->bind(FinderInterface::class)->to(Finder::class);
        $this->bind(MovieListerInterface::class)->toConstructor(
            MovieLister::class,
            '',
            (new InjectionPoints)->addMethod('setFinder') // or (new InjectionPoints)->addOptionalMethod('setFinder')
        );
    }
}
$injector = new Injector(new Lister2Module);
$lister = $injector->getInstance(MovieListerInterface::class);
/* @var $lister MovieLister */
$works = ($lister->finder instanceof FinderInterface);
echo($works ? 'It works!' : 'It DOES NOT work!') . PHP_EOL;
