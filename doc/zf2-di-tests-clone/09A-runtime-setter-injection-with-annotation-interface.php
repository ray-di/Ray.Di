<?php

namespace MovieApp {

use Ray\Di\Di\Inject;

    class Lister
    {
        public $finder;

        /**
         * @Inject
         */
        public function setFinder(FinderInterface $finder)
        {
            $this->finder = $finder;
        }
    }

    interface FinderInterface
    {
    }

    class Finder implements FinderInterface
    {
    }
}

namespace {

    $di = include __DIR__ . '/scripts/instance.php';

    class Module extends \Ray\Di\AbstractModule
    {
        public function configure()
        {
            $this->bind('MovieApp\FinderInterface')->to('MovieApp\Finder');
        }
    }
    $di->setModule(new Module);
    $lister = $di->getInstance('MovieApp\Lister');

    // expression to test
    $works = ($lister->finder instanceof MovieApp\Finder);

    // display result
    echo (($works) ? 'It works!' : 'It DOES NOT work!');
}
