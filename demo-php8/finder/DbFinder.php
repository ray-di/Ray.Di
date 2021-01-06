<?php

declare(strict_types=1);

use Ray\Di\Di\Inject;

class DbFinder implements FinderInterface
{
    public function __construct(DbInterface $db)
    {
    }

    #[Inject]
    public function setDb(DbInterface $db)
    {
    }

    #[Inject]
    public function setSorter(Sorter $sorter, Sorter $sorte2)
    {
    }
}
