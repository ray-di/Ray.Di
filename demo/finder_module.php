<?php

declare(strict_types=1);

use Ray\Di\AbstractModule;
use Ray\Di\Di\Inject;
use Ray\Di\Di\PostConstruct;
use Ray\Di\Scope;

class Sorter
{
}

interface DbInterface
{
}

class Db implements DbInterface
{
    public function __construct($dsn, $username, $password)
    {
    }

    /**
     * @PostConstruct
     */
    public function init()
    {
    }
}

interface FinderInterface
{
}

class DbFinder implements FinderInterface
{
    public function __construct(DbInterface $db)
    {
    }

    /**
     * @Inject
     */
    public function setDb(DbInterface $db)
    {
    }

    /**
     * @Inject
     */
    public function setSorter(Sorter $sorter, Sorter $sorte2)
    {
    }
}

interface MovieListerInterface
{
}

class MovieLister implements MovieListerInterface
{
    public function __construct(FinderInterface $finder)
    {
    }

    /** @Inject */
    public function setFinder01(FinderInterface $finder, FinderInterface $finder1, FinderInterface $finder2)
    {
    }
}

class FinderModule extends AbstractModule
{
    protected function configure()
    {
        $this->bind(Sorter::class)->in(Scope::SINGLETON);
        $this->bind(DbInterface::class)->toConstructor(Db::class, 'dsn=dsn,username=username,password=password');
        $this->bind()->annotatedWith('dsn')->toInstance('msql:host=localhost;dbname=test');
        $this->bind()->annotatedWith('username')->toInstance('root');
        $this->bind()->annotatedWith('password')->toInstance('');
        $this->bind(FinderInterface::class)->to(DbFinder::class);
        $this->bind(MovieListerInterface::class)->to(MovieLister::class);
    }
}
