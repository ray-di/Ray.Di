<?php

namespace Ray\Di\Demo;

require __DIR__ . '/bootstrap.php';

use Ray\Di\Injector;
use Ray\Di\AbstractModule;

class ConstructorBindingModule extends AbstractModule
{
    protected function configure()
    {
        $this->bind()->annotatedWith('php_version')->toInstance('4.4');
        $this->bind(LangInterface::class)->toConstructor(Php::class, 'version=php_version');
    }
}

$injector = new Injector(new ConstructorBindingModule);
$php = $injector->getInstance(LangInterface::class);
/** @var $php Php */
$works = $php->version === '4.4';

echo ($works ? 'It works!' : 'It DOES NOT work!') . PHP_EOL;
