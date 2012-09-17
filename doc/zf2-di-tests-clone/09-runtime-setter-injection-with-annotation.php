<?php
namespace MovieApp {

    use Ray\Di\Di\Inject;
    use Ray\Di\Di\Named;

    class Lister
    {
        public $finder;

        /**
         * @Inject
         */
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
    $lister = $di->getInstance('MovieApp\Lister');

    // expression to test
    $works = ($lister->finder instanceof MovieApp\Finder);

    // display result
    echo (($works) ? 'It works!' : 'It DOES NOT work!');
}
