<?php

namespace Ray\Di\Test\Sample;

use Ray\Di\Injector;

$loader = require dirname(dirname(__DIR__)) . '/vendor/autoload.php';
\Doctrine\Common\Annotations\AnnotationRegistry::registerLoader([$loader, 'loadClass']);
require __DIR__ . '/src.php';

$injector = new Injector(new ListerModule);
$movieLister = $injector->getInstance(MovieListerInterface::class);

$works = ($movieLister->finder instanceof FinderInterface);
echo (($works) ? 'It works!' : 'It DOES NOT work!') . PHP_EOL;
