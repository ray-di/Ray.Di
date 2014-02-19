<?php

namespace MovieApp {
    class Lister
    {
        public $finder;
        public function __construct(Finder $finder)
        {
            $this->finder = $finder;
        }
    }
    class Finder {}
}

namespace {
    $di = require __DIR__ . '/scripts/instance.php';
    $di->getContainer()->params['MovieApp\Lister'] = [
        'finder' => new MovieApp\Finder
    ];
    $lister = $di->getInstance('MovieApp\Lister');

    // expression to test
    $works = ($lister->finder instanceof MovieApp\Finder);

    // display result
    echo (($works) ? 'It works!' : 'It DOES NOT work!');
}
