<?php

declare(strict_types=1);

use Ray\Di\AbstractModule;
use Ray\Di\InjectionPointInterface;
use Ray\Di\Injector;
use Ray\Di\ProviderInterface;

require dirname(__DIR__) . '/vendor/autoload.php';

interface FinderInterface
{
}

interface MovieListerInterface
{
}

class Finder implements FinderInterface
{
    private string $className;

    public function __construct(string $className)
    {
        $this->className = $className;
    }

    public function find(): string
    {
        return sprintf('search for [%s]', $this->className);
    }
}

class MovieLister implements MovieListerInterface
{
    public FinderInterface $finder;

    public function __construct(FinderInterface $finder)
    {
        $this->finder = $finder;
    }
}

class FinderProvider implements ProviderInterface
{
    public function __construct(
        public InjectionPointInterface $ip
    )
    {}

    /**
     * {@inheritdoc}
     */
    public function get()
    {
        $className = $this->ip->getClass()->getName();

        return new Finder($className);
    }
}

class FinderModule extends AbstractModule
{
    protected function configure()
    {
        $this->bind(FinderInterface::class)->toProvider(FinderProvider::class);
        $this->bind(MovieListerInterface::class)->to(MovieLister::class);
    }
}

$injector = new Injector(new FinderModule());
$movieLister = $injector->getInstance(MovieListerInterface::class);
/** @var MovieLister $movieLister */
$result = $movieLister->finder->find();
$works = ($result === 'search for [MovieLister]');

echo($works ? 'It works!' : 'It DOES NOT work!') . PHP_EOL;
