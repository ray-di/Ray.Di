## Grapher

In `Grapher`, constructor arguments are passed manually and subsequent injections are done automatically.
It is useful to introduce Ray.Di into an existing system (where only root objects have an object generation mechanism).

```php
// ...
$grapher = new Grapher(new Module, __DIR__ . '/tmp');
$instance = $grapher->newInstanceArgs(FooController::class, [$param1, $param2]);
```

## Graphing Ray.Di Applications

When you've written a sophisticated application, Ray.Di rich introspection API can describe the object graph in detail. The object-visual-grapher exposes this data as an easily understandable visualization. It can show the bindings and dependencies from several classes in a complex application in a unified diagram.

![fake](https://user-images.githubusercontent.com/529021/72650686-866ec100-39c4-11ea-8b49-2d86d991dc6d.png)

See more at https://github.com/koriym/Ray.ObjectGrapher

