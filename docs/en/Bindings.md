# Bindings

A binding is an object that corresponds to an entry in Ray.Di map. You add new entries into the Ray.Di map by creating bindings.

## Creating Bindings

To create bindings, extend `AbstractModule` and override its `configure` method. In the method body, call `bind()` to specify each binding. These methods are type checked in compile can report errors if you use the wrong types. Once you've created your modules, pass them as arguments to `Injector` to build an injector.

Use modules to create linked bindings, instance bindings, provider bindings, constructor bindings and untargetted bindings.

```php
class TweetModule extends AbstractModule
{
    protected function configure()
    {
        $this->bind(TweetClient::class);
        $this->bind(TweeterInterface::class)->to(SmsTweeter::class)->in(Scope::SINGLETON);
        $this->bind(UrlShortenerInterface)->toProvider(TinyUrlShortener::class)
        $this->bind('')->annotatedWith(Username::class)->toInstance("koriym")
    }
}
```

More Bindings

In addition to the bindings you specify the injector includes [built-in bindings](BuiltinBindings.md). When a dependency is requested but not found it attempts to create a just-in-time binding. The injector also includes bindings for the providers of its other bindings.

## Module Install

A module can install other modules to configure more bindings.

* Earlier bindings have priority even if the same binding is made later.
* `override` bindings in that module have priority.

```php
protected function configure()
{
    $this->install(new OtherModule);
    $this->override(new CustomiseModule);
}
```
