<?php

namespace MovieApp {
    class Lister
    {
        public $finder;
        public function setFinder(Finder $finder)
        {
            $this->finder = $finder;
        }
    }
    class Finder
    {
    }
}

namespace {
    $di = include __DIR__ . '/scripts/instance.php';
    $di->getContainer()->setter['MovieApp\Lister']['setFinder'] = [
        'finder' => new MovieApp\Finder
    ];
    $lister = $di->getInstance('MovieApp\Lister');

    // expression to test
    $works = ($lister->finder instanceof MovieApp\Finder);

    // display result
    echo (($works) ? 'It works!' : 'It DOES NOT work!');
}
