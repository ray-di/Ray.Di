<?php

namespace Ray\Di\Demo;

use Ray\Di\Injector;
use Ray\Di\AbstractModule;

require __DIR__ . '/bootstrap.php';

class ProviderBindingModule extends AbstractModule
{
    protected function configure()
    {
        $this->bind(LangInterface::class)->toProvider(PhpProvider::class);
        $this->bind()->annotatedWith('php_version')->toInstance('7.0');
    }
}

$injector = new Injector(new ProviderBindingModule);
$computer = $injector->getInstance(Computer::class);
/* @var $computer Computer */
$works = ($computer->lang instanceof Php) && $computer->lang->version === '7.0';

echo ($works ? 'It works!' : 'It DOES NOT work!') . PHP_EOL;
