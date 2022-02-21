# Ray.Di Mental Model

_Learn about `Key`, `Provider` and how Ray.Di is just a map_

When you are reading about "Dependency Injection", you often see many buzzwords ("Inversion of
control", "Hollywood principle") that make it sound confusing. But
underneath the jargon of dependency injection, the concepts aren't very
complicated. In fact, you might have written something very similar already!
This page walks through a simplified model of Ray.Di implementation, which
should make it easier to think about how it works.



## Ray.Di is a map

Fundamentally, Ray.Di helps you create and retrieve objects for your application
to use. These objects that your application needs are called **dependencies**.

You can think of Ray.Di as being a map[^Ray.Di-map]. Your application code
declares the dependencies it needs, and Ray.Di fetches them for you from its map.
Each entry in the "Ray.Di map" has two parts:

*   **Ray.Di key**: a key in the map which is used to fetch a particular value
    from the map.
*   **Provider**: a value in the map which is used to create objects for your
    application.

Ray.Di keys and Providers are explained below.

[^Ray.Di-map]: The actual implementation of Ray.Di is far more complicated, but a
map is a reasonable approximation for how Ray.Di behaves.

### Ray.Di keys

Ray.Di uses [`Dependecy Index`] to identify a dependency that can be resolved using the
"Ray.Di map".

The `Greeter` class used in the [Getting Started](GettingStarted.md) declares two
dependencies in its constructor and those dependencies are represented as `Key`
in Ray.Di:

*   `#[Message] string` --> `(string) $map[$messageIndex]`
*   `#[Count] int` --> `(int) $map[$countIndex]`

The simplest form of a `Key` represents a type in php:

```php
// Identifies a dependency that is an instance of string.
/** @var string $databaseKey */
$databaseKey = $map[$index];
```

However, applications often have dependencies that are of the same type:

```php
final class MultilingualGreeter
{
    public function __construct(
      private readonly string $englishGreeting,
      private readonly string $spanishGreeting
    ) {}
}
```

Ray.Di uses [binding Attributes](BindingAttributes.md) to distinguish dependencies
that are of the same type, that is to make the type more specific:

```php
final class MultilingualGreeter
{
    public function __construct(
      #[English] private readonly string $englishGreeting,
      #[Spanish] private readonly string $spanishGreeting
    ) {}
}
```

`Key` with binding annotations can be created as:

```php
$englishGreetingKey = $map[English::class];
$spanishGreetingKey = $map[Spanish::class];
```

When an application calls `$injector->getInstance(MultilingualGreeter::class)` to
create an instance of `MultilingualGreeter`. This is the equivalent of doing:

```java
// Ray.Di internally does this for you so you don't have to wire up those
// dependencies manually.
/** @var string $english */
$english = $injector->getInstance('', English::class));
/** @var string $spanish */
$spanish = $injector->getInstance('', Spanish::class));
/** @var MultilingualGreeter $greeter */
$greeter = new MultilingualGreeter($english, $spanish);
```

To summarize: **Ray.Di `Key` is a type combined with an optional binding
annotation used to identify dependencies.**

### Ray.Di `Provider`s

Ray.Di uses
[`Provider`](https://google.github.io/Ray.Di/api-docs/latest/javadoc/com/google/inject/Provider.html)
to represent factories in the "Ray.Di map" that are capable of creating objects
to satisfy dependencies.

`Provider` is an interface with a single method:

```php
interface Provider
{
  /** Provides an instance/
  public function get();
}
```

Each class that implements `Provider` is a bit of code that knows how to give
you an instance of `T`. It could call `new T()`, it could construct `T` in some
other way, or it could return you a precomputed instance from a cache.

Most applications do not implement `Provider` interface directly, they use
`Module` to configure Ray.Di injector and Ray.Di injector internally creates
`Provider`s for all the object it knows how to create.

For example, the following Ray.Di module creates two `Provider`s:

```php
class countProvicer implements ProviderInterface
{
    public function get(): int
    {
        return 3;
    }
}

class messageProvider implements ProviderInterface
{
    public function get(): string
    {
        return 'hello world';
    }
}

class DemoModule extends AbstractModule
{
   protected function configure(): void
   {
       $this->bind()->annotatedWith(Count::class)->toProvider(CountProvicer::class);
       $this->bind()->annotatedWith(Message::class)->toProvider(MessageProvicer::class);
   }
}
```

*   `MessageProvicer` that calls the `get()` method and returns "hello
    world"
*   `CountProvicer` that calls the `get()` method and returns `3`

## Using Ray.Di

There are two parts to using Ray.Di:

1.  **Configuration**: your application adds things into the "Ray.Di map".
1.  **Injection**: your application asks Ray.Di to create and retrieve objects
    from the map.

Configuration and injection are explained below.

### Configuration

Ray.Di maps are configured using Ray.Di modules. A **Ray.Di module** is a unit of
configuration logic that adds things into the Ray.Di map. There are two ways to
do this:

*   Adding method annotations like `@Provides`
*   Using the Ray.Di Domain Specific Language (DSL).

Conceptually, these APIs simply provide ways to manipulate the Ray.Di map. The
manipulations they do are pretty straightforward. Here are some example
translations, shown using Java 8 syntax for brevity and clarity:

<!-- mdformat off(Multiline table is not supported in Github) -->
| Ray.Di DSL syntax                   | Mental model                                                                       |
| ---------------------------------- | ---------------------------------------------------------------------------------- |
| `bind($key)->toInstance($value)`  | `$map[$key] = $value;`  <br>(instance binding)          |
| `bind($key)->toProvider($provider)` | `$map[$key] = fn => $value;` <br>(provider  binding) |
| `bind(key)->to(anotherKey)`       | `$map[$key] = $map[$anotherKey];` <br>(linked binding) |
|<!-- mdformat on -->||

`DemoModule` adds two entries into the Ray.Di map:

*   `#[Message] string` --> `fn() => MessageProvicer::get()`
*   `#[Count] int` --> `fn() -> CountProvicer::get()`

### Injection

You don't *pull* things out of a map, you *declare* that you need them. This is
the essence of dependency injection. If you need something, you don't go out and
get it from somewhere, or even ask a class to return you something. Instead, you
simply declare that you can't do your work without it, and rely on Ray.Di to give
you what you need.

This model is backwards from how most people think about code: it's a more
*declarative* model rather than an *imperative* one. This is why dependency
injection is often described as a kind of *inversion of control* (IoC).

Some ways of declaring that you need something:

1. An argument to a constructor:

    ```php
    class Foo
    {
      // We need a database, from somewhere
      public function __construct(
            private Database $database
       ) {}
    }
    ```

2. An argument to a `DatabaseProvider::get()` method:

    ```php
    class DatabaseProvider implements ProviderInterface
    {
        public function __construct(
            #[Dsn] private string $dsn
        ){}
      
        public function get(): Database
        {
            return new Database($this->dsn);
        }
    }
    ```

This example is intentionally the same as the example `Foo` class from
[Getting Started Guide](GettingStarted#what-is-dependency-injection), adding
only the `@Inject` annotation on the constructor, which marks the constructor as
being available for Ray.Di to use.

## Dependencies form a graph

When injecting a thing that has dependencies of its own, Ray.Di recursively
injects the dependencies. You can imagine that in order to inject an instance of
`Foo` as shown above, Ray.Di creates `Provider` implementations that look like
these:

```php
class FooProvider implements Provider
{
    public function get(): Foo
    {
        global $map;
        
        $databaseProvider = $map[Database::class]);
        $database = $databaseProvider->get();
        
        return new Foo($database);
    }
}

class DatabaseProvider implements Provider
{
    public function get(): Database
    {
        global $map;
        
        $dsnProvider = $map[Dsn::class];
        $dsn = $dsnProvider->get();
        
        return new Database($dsn);
    }
}  

class DsnProvider implements Provider
{
    public function get(): string
    {
        return getenv(DB_DSN);
    }
}  
```

Dependencies form a *directed graph*, and injection works by doing a depth-first
traversal of the graph from the object you want up through all its dependencies.

A Ray.Di `Injector` object represents the entire dependency graph. To create an
`Injector`, Ray.Di needs to validate that the entire graph works. There can't be
any "dangling" nodes where a dependency is needed but not provided.[^3] If the
graph is invalid for any reason, Ray.Di throws a `CreationException` that
describes what went wrong.

[^3]: The reverse case is not an error: it's fine to provide something even if
nothing ever uses it—it's just dead code in that case. That said, just
like any dead code, it's best to delete providers if nobody uses them
anymore.

## What's next?

Learn how to use [`Scopes`](Scopes) to manage the lifecycle of objects created
by Ray.Di and the many different ways to
[add entries into the Ray.Di map](Bindings).

