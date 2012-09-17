<?php

namespace Foo\Bar {
    class Bam{}
}

namespace Foo\Bar {

    use Ray\Di\Di\Inject;
    use Ray\Di\Di\Named;

    class Baz
    {
        public $bam;

        /**
         * @Inject
         */
        public function setBam(Bam $bam)
        {
            $this->bam = $bam;
        }
    }
}

namespace Foo\Bar\Test {

    $di = include __DIR__ . '/scripts/instance.php';
    $baz = $di->getInstance('Foo\Bar\Baz');
    // expression to test
    $works = ($baz->bam instanceof \Foo\Bar\Bam);

    // display result
    echo (($works) ? 'It works!' : 'It DOES NOT work!');
}
