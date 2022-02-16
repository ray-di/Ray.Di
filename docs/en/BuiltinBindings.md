# Built-in Bindings

_More bindings that you can use_

**NOTE**: It's very rare that you'd need to use those built-in bindings.

## The Injector

In framework code, sometimes you don't know the type you need until runtime. In
this rare case you should inject the injector. Code that injects the injector
does not self-document its dependencies, so this approach should be done
sparingly.

## Providers

For every type Guice knows about, it can also inject a Provider of that type.
[Injecting Providers](InjectingProviders.md) describes this in detail.
