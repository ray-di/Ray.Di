<?php

namespace Ray\Di\Demo;

use Ray\Di\Injector;
use Ray\Di\AbstractModule;

require __DIR__ . '/bootstrap.php';
require __DIR__ . '/src/modules.php';

class InstallModule extends AbstractModule
{
    protected function configure()
    {
        $this->install(new LinkedBindingModule);
        $this->install(new ProviderBindingModule);
        $this->install(new BindingAnnotationModule);
        $this->install(new ConstructorBindingModule);
    }
}

$file = __FILE__ . '.cache';
$injector = file_exists($file) ? unserialize(file_get_contents($file)) : new Injector(new InstallModule);
$robot1 = $injector->getInstance(Robot::class);

// save file cache
if ($injector->isUpdated()) {
    echo 'save' . PHP_EOL;
    file_put_contents($file ,serialize($injector));
}

$works = $robot1->isReady === true;
echo ($works ? 'It works!' : 'It DOES NOT work!') . PHP_EOL;
