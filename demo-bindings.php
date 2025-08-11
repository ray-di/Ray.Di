<?php

declare(strict_types=1);

use Ray\Di\AbstractModule;
use Ray\Di\Container;
use Ray\Di\Injector;
use Ray\Di\ModuleBindings;
use Ray\Di\ModuleString;
use Ray\Di\Fake\FakeAopInterface;
use Ray\Di\Fake\FakeAop;
use Ray\Di\FakeDoubleInterceptor;

require __DIR__ . '/vendor/autoload.php';

// Example module with AOP
class DemoAopModule extends AbstractModule
{
    protected function configure(): void
    {
        // Standard binding
        $this->bind(FakeAopInterface::class)->to(FakeAop::class);
        
        // Named binding
        $this->bind(FakeAopInterface::class)
            ->annotatedWith('special')
            ->to(FakeAop::class);
        
        // AOP binding - bind interceptor to all methods of FakeAop
        $this->bindInterceptor(
            $this->matcher->subclassesOf(FakeAop::class),
            $this->matcher->any(),
            [FakeDoubleInterceptor::class]
        );
    }
}

// Create injector and get container
$module = new DemoAopModule();
$injector = new Injector($module);
$container = $injector->getContainer();

// Get pointcuts for AOP
$pointcuts = $container->getPointcuts();

echo "=== Traditional ModuleString Output ===\n";
$moduleString = new ModuleString();
$output = $moduleString($container, $pointcuts);
echo $output . "\n\n";

echo "=== Machine-Readable ModuleBindings Output ===\n";
$moduleBindings = new ModuleBindings();
$bindings = $moduleBindings($container, $pointcuts);

// Display as formatted JSON
$json = json_encode($bindings, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);
echo $json . "\n\n";

// Show specific examples
echo "=== Individual Binding Examples ===\n";
foreach ($bindings as $binding) {
    echo sprintf(
        "Interface: %s\n  Named: %s\n  Type: %s\n  Target: %s\n",
        $binding['interface'],
        $binding['named'] ?? '(none)',
        $binding['type'],
        is_string($binding['target']) ? $binding['target'] : json_encode($binding['target'])
    );
    
    if (isset($binding['aop'])) {
        echo "  AOP Bindings:\n";
        foreach ($binding['aop'] as $method => $interceptors) {
            echo sprintf(
                "    Method '%s': %s\n",
                $method,
                implode(', ', $interceptors)
            );
        }
    }
    echo "\n";
}