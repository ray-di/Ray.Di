<?php

namespace Foo\Bar {
    class Bam{}
}

namespace Foo\Bar {
    class Baz {
        public $bam;

        /**
         * @Inject
         */
        public function setBam(Bam $bam){
            $this->bam = $bam;
        }
    }
}

namespace Foo\Bar\Test {

    $di = include __DIR__ . '/scripts/instance.php';
    $baz = $di->getInstance('Foo\Bar\Baz');
    var_dump($baz);
    // expression to test
    $works = ($baz->bam instanceof \Foo\Bar\Bam);

    // display result
    echo (($works) ? 'It works!' : 'It DOES NOT work!');
}