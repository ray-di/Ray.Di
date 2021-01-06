## Performance boost ##

### Script injector

`ScriptInjector` generates raw factory code for better performance and to clarify how the instance is created.

```php

use Ray\Di\ScriptInjector;
use Ray\Compiler\DiCompiler;
use Ray\Compiler\Exception\NotCompiled;

try {
    $injector = new ScriptInjector($tmpDir);
    $instance = $injector->getInstance(ListerInterface::class);
} catch (NotCompiled $e) {
    $compiler = new DiCompiler(new ListerModule, $tmpDir);
    $compiler->compile();
    $instance = $injector->getInstance(ListerInterface::class);
}
```
Once an instance has been created, You can view the generated factory files in `$tmpDir`

### Cache injector

The injector is serializable.
It also boosts the performance.

```php

// save
$injector = new Injector(new ListerModule);
$cachedInjector = serialize($injector);

// load
$injector = unserialize($cachedInjector);
$lister = $injector->getInstance(ListerInterface::class);

```

### CachedInjectorFactory

The `CachedInejctorFactory` can be used in a hybrid of the two injectors to achieve the best performance in both development and production.

The injector is able to inject singleton objects **beyond the request**, greatly increasing the speed of testing. Successive PDO connections also do not run out of connection resources in the test.

See [CachedInjectorFactory](https://github.com/ray-di/Ray.Compiler/issues/75) for more information.
