<?php

declare(strict_types=1);

use Ray\Di\AbstractModule;
use Ray\Di\Injector;
use Ray\Di\ProviderInterface;

require dirname(__DIR__) . '/vendor/autoload.php';

interface FinderInterface
{
}

class Finder implements FinderInterface
{
    public $datetime;

    public function __construct(DateTimeInterface $dateTime)
    {
        $this->datetime = $dateTime;
    }
}

class FinderProvider implements ProviderInterface
{
    /**
     * {@inheritdoc}
     */
    public function get()
    {
        return new Finder(new DateTimeImmutable('now'));
    }
}

interface MovieListerInterface
{
}

class MovieLister implements MovieListerInterface
{
    public function __construct(
        public FinderInterface $finder
    ){
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
$works = ($movieLister->finder->datetime instanceof DateTimeImmutable);

echo($works ? 'It works!' : 'It DOES NOT work!') . PHP_EOL;
