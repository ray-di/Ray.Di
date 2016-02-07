<?php

namespace Ray\Di\Demo;

use Ray\Di\InjectionPoints;
use Ray\Di\Injector;
use Ray\Di\AbstractModule;

require __DIR__ . '/bootstrap.php';

interface FinderInterface {}
interface ListerInterface {}

class Finder implements FinderInterface {}
class Lister implements ListerInterface
{
    public $finder;

    /**
     * Constructor Injection
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
        $this->bind(ListerInterface::class)->to(Lister::class);
    }
}

$injector = new Injector(new ListerModule);
$lister = $injector->getInstance(ListerInterface::class);
/* @var $lister Lister */
$works = ($lister->finder instanceof FinderInterface);
echo ($works ? 'It works!' : 'It DOES NOT work!') . PHP_EOL;

class Lister2 implements ListerInterface
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
        $this->bind(ListerInterface::class)->toConstructor(
            Lister2::class,
            '',
            (new InjectionPoints)->addMethod('setFinder') // or (new InjectionPoints)->addOptionalMethod('setFinder')
        );
    }
}
$injector = new Injector(new Lister2Module);
$lister = $injector->getInstance(ListerInterface::class);
/* @var $lister Lister */
$works = ($lister->finder instanceof FinderInterface);
echo ($works ? 'It works!' : 'It DOES NOT work!') . PHP_EOL;
