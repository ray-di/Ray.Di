_How to start doing dependency injection with Ray.Di._

## Getting Started

Ray.Di is a framework that makes it easier for your application to use the dependency injection (DI) pattern. This getting started guide will walk you through a simple example of how you can use Ray.Di to incorporate dependency injection into your application.

### What is dependency injection?

[Dependency injection](https://en.wikipedia.org/wiki/Dependency_injection) is a design pattern wherein classes declare their dependencies as arguments instead
of creating those dependencies directly. For example, a client that wishes to call a service should not have to know how to construct the service, rather, some external code is responsible for providing the service to the client.

Here's a simple example of code that *does not* use dependency injection:

```php
class Foo
{
    private Database $database;  // We need a Database to do some work
    
    public function __construct()
    {
        // Ugh. How could I test this? What if I ever want to use a different
        // database in another application?
        $this->database = new Database("/path/to/my/data");
    }
}
```

The `Foo` class above creates a fixed `Database` object directly. This prevents this class from being used with other `Database` objects and does not allow the real database to be swapped out for a testing database in tests. Instead of writing untestable or inflexible code, you can use dependency injection pattern
to address all these issues.

Here's the same example, this time using dependency injection:

```php
class Foo {
    private Database $database;  // We need a Database to do some work
    
    public function __construct(Database $database)
    {
        // The database comes from somewhere else. Where? That's not my job, that's
        // the job of whoever constructs me: they can choose which database to use.
        $this->database = $database;
    }
}
```

The `Foo` class above can be used with any `Database` objects since `Foo` has no knowledge of how the `Database` is created. For example, you can create a test version of `Database` implementation that uses an in-memory database in tests to make the test hermetic and fast.

The [Motivation](Motivation.md) page explains why applications should use the dependency injection pattern in more detail.

## Core Ray.Di concepts

### constructor

PHP class constructors can be called by Ray.Di through a process called [constructor injection](Injections.md#constructor-injection), during which the constructors' arguments will be created and provided by Ray.Di. (Unlike Guice, Ray.Di does not require the "Inject" annotation in its constructor.)

Here is an example of a class that uses constructor injection:

```php
class Greeter
{
    // Greeter declares that it needs a string message and an integer
    // representing the number of time the message to be printed.
    // The @Inject annotation marks this constructor as eligible to be used by
    // Ray.Di.
    public function __construfct(
        #[Message] readonly string $message,
        #[Count] readonly int $count
    ) {}

    public function sayHello(): void
    {
        for ($i=0; $i < $this->count; $i++) {
            echo $message;
        }
    }
}
```

In the example above, the `Greeter` class has a constructor that is called whenapplication asks Ray.Di to create an instance of `Greeter`. Ray.Di will create the two arguments required, then invoke the constructor. The `Greeter` class's constructor arguments are its dependencies and applications use `Module` to tell Ray.Di how to satisfy those dependencies.

### Ray.Di modules

Applications contain objects that declare dependencies on other objects, and those dependencies form graphs. For example, the above `Greeter` class has two dependencies (declared in its constructor):

*   A `string` value for the message to be printed
*   An `int` value for the number of times to print the message

Ray.Di modules allow applications to specify how to satisfy those dependencies. For example, the following `DemoModule` configures all the necessary dependencies for `Greeter` class:

```php
/**
 * Ray.Di module that provides bindings for message and count used in
 * {@link Greeter}.
 */
class DemoModule extends AbstractModule
{
    protected function configure(): void
    {
        $this->bind()->annotatedWith(Count::class)->toInstance(3);
        $this->bind()->annotatedWith(Message::class)->toInstance('hello world');
    }
}
```

In a real application, the dependency graph for objects will be much more complicated and Ray.Di makes creating complex object easy by creating all the transitive dependencies automatically.

### Ray.Di injectors

To bootstrap your application, you'll need to create a Ray.Di # [`Injector`] withone or more modules in it. For example, a web server application might have a`main` method that looks like this:

```php
public final class MyWebServer {
  public function start(): void
  {
    //　...
  }

  public function __invoke(): void
  {
    // Creates an injector that has all the necessary dependencies needed to
    // build a functional server.
    $injector = new Injector(
        new RequestLoggingModule(),
        new RequestHandlerModule(),
        new AuthenticationModule(),
        new DatabaseModule(),
        // ...
    );
    // Bootstrap the application by creating an instance of the server then
    // start the server to handle incoming requests.
    $injector->getInstance(MyWebServer::class)->start();
  }
}

(new MyWebServer)();
```

The injector internally holds the dependency graphs described in your application. When you request an instance of a given type, the injector figures out what objects to construct, resolves their dependencies, and wires everything together. To specify how dependencies are resolved, configure your injector with
[bindings](Bindings).

[`Injector`]: https://google.github.io/guice/api-docs/latest/javadoc/com/google/inject/Injector.html

## A simple Ray.Di application

The following is a simple Ray.Di application with all the necessary pieces put
together:

```php
<?php
require __DIR__ . '/vendor/autoload.php';

use Ray\Di\AbstractModule;
use Ray\Di\Di\Qualifier;
use Ray\Di\Injector;

#[Attribute, Qualifier]
class Message
{
}

#[Attribute, Qualifier]
class Count
{
}

class DemoModule extends AbstractModule
{
    protected function configure()
    {
        $this->bind()->annotatedWith(Message::class)->toInstance('hello world');
        $this->bind()->annotatedWith(Count::class)->toInstance(3);
    }
}

class Greeter
{
    public function __construct(
        #[Greeting] private string $greerting,
        #[Count] private int $count
    ) {}

    public function sayHello(): void
    {
        for ($i = 0; $i < $this->count ; $i++) {
            echo $this->greerting . PHP_EOL;
        }
    }
}

/*
 * Injector's constructor takes one modules.
 * Most applications will call this method exactly once in bootstrap.
 */
$injector = new Injector(new DemoModule);

/*
 * Now that we've got the injector, we can build objects.
 */
$greeter = $injector->getInstance(Greeter::class);

// Prints "hello world" 3 times to the console.
$greeter->sayHello();
```

The `RayDiDemo` application constructed a small dependency graph using Ray.Di
that is capable of building instances of `Greeter` class. Large applications
usually have many `Module`s that can build complex objects.

## What's next?

Read more on how to conceptualize Ray.Di with a simple [mental model](MentalModel.md).
