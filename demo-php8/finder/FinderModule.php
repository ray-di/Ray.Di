<?php

declare(strict_types=1);

use Ray\Di\AbstractModule;
use Ray\Di\Scope;

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
