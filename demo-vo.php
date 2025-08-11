<?php

require __DIR__ . '/vendor/autoload.php';

use Ray\Di\AbstractModule;
use Ray\Di\ModuleBindings;

$module = new class extends AbstractModule {
    protected function configure(): void
    {
        $this->bind('')->annotatedWith('app_name')->toInstance('MyApp');
        $this->bind('')->annotatedWith('version')->toInstance('1.0.0');
    }
};

$container = $module->getContainer();
$moduleBindings = new ModuleBindings();
$bindings = $moduleBindings($container, []);

echo "=== BindingInfo Value Objects ===\n";
foreach ($bindings as $binding) {
    echo sprintf(
        "Interface: '%s', Named: '%s', Type: %s, Target: %s\n",
        $binding->interface,
        $binding->named ?? 'null',
        $binding->type,
        json_encode($binding->target)
    );
}

echo "\n=== JSON Serialized ===\n";
echo json_encode($bindings, JSON_PRETTY_PRINT);