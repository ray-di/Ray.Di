<?php

declare(strict_types=1);
/**
 * This file is part of the Ray.Di package.
 *
 * @license http://opensource.org/licenses/MIT MIT
 */
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

class Foo
{
    /**
     * @Assisted({"finder"})
     */
    public function bar($name, FinderInterface $finder)
    {
        return sprintf('searching [%s] by [%s]...', $name, get_class($finder));
    }
}

$injector = new Injector(new FinderModule());
$foo = $injector->getInstance(Foo::class);
/* @var $foo Foo */
echo $foo->bar('Tokyo Story');

// searching [Tokyo Story] by [Finder]...
