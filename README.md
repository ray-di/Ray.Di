# Ray.Di

## Dependency Injection framework #

[![Scrutinizer Code Quality](https://scrutinizer-ci.com/g/ray-di/Ray.Di/badges/quality-score.png?b=2.x)](https://scrutinizer-ci.com/g/ray-di/Ray.Di/?branch=2.x)
[![Code Coverage](https://scrutinizer-ci.com/g/ray-di/Ray.Di/badges/coverage.png?b=2.x)](https://scrutinizer-ci.com/g/ray-di/Ray.Di/?branch=2.x)
[![Build Status](https://travis-ci.org/ray-di/Ray.Di.svg?branch=2.x)](https://travis-ci.org/ray-di/Ray.Di)

**Ray.Di** was created in order to get Guice style dependency injection in PHP projects. It tries to mirror Guice's behavior and style. [Guice](http://code.google.com/p/google-guice/wiki/Motivation?tm=6) is a Java dependency injection framework developed by Google.

## Getting Stated #

### Linked Bindings ##

Here is a basic example of dependency injection using Ray.Di.

```php
namespace MovieApp;

use Ray\Di\AbstractModule;
use Ray\Di\Di\Inject;
use Ray\Di\Injector;
use MovieApp\FinderInterface;
use MovieApp\Finder;

interface FinderInterface
{
}

interface ListerInterface
{
}

class Finder implements FinderInterface
{
}

class Lister implements ListerInterface
{
    public $finder;

    /**
     * @Inject
     */
    public function setFinder(FinderInterface $finder)
    {
        $this->finder = $finder;
    }
}

class ListerModule extends AbstractModule
{
    public function configure()
    {
        $this->bind(FinderInterface::class)->to(Finder::class);
        $this->bind(ListerInterface::class)->to(Lister::class);
    }
}

$injector = new Injector(new ListerModule);
$lister = $injector->getInstance(ListerInterface::class);
$works = ($lister->finder instanceof Finder::class);
echo(($works) ? 'It works!' : 'It DOES NOT work!');

// It works!
```
This is an example of **Linked Bindings**. Linked bindings map a type to its implementation, it can also be chained.

### Provider Bindings ##

[Provider bindings](http://code.google.com/p/rayphp/wiki/ProviderBindings) map a type to its provider.

```php
$this->bind(TransactionLogInterface::class)->toProvider(DatabaseTransactionLogProvider::class);
```
The provider class implements Ray's Provider interface, which is a simple, general interface for supplying values:

```php
namespace Ray\Di;

interface ProviderInterface
{
    public function get();
}
```
Our provider implementation class has dependencies of its own, which it receives via a contructor annotated with `@Inject`.
It implements the Provider interface to define what's returned with complete type safety:

```php

use Ray\Di\Di\Inject;
use Ray\Di\ProviderInterface;

class DatabaseTransactionLogProvider implements ProviderInterface
{
    private $connection;

    /**
     * @Inject
     */
    public function __construct(ConnectionInterface $connection)
    {
        $this->connection = $connection;
    }

    public function get()
    {
        $transactionLog = new DatabaseTransactionLog;
        $transactionLog->setConnection($this->connection);

        return $transactionLog;
    }
}
```

Finally we bind to the provider using the `toProvider()` method:

```php
$this->bind(TransactionLogInterface::class)->toProvider(DatabaseTransactionLogProvider::class);
```

### Binding Annotations ##

Occasionally you'll want multiple bindings for a same type. For example, you might want both a PayPal credit card processor and a Google Checkout processor.
To enable this, bindings support an optional binding annotation. The annotation and type together uniquely identify a binding. This pair is called a key.

Define qualifier annotation first. It needs to be annotated with `@Qualifier` annotation.

```php

use Ray\Di\Di\Qualifier;

/**
 * @Annotation
 * @Target("METHOD")
 * @Qualifier
 */
final class PayPal
{
}
```

To depend on the annotated binding, apply the annotation to the injected parameter:

```php
/**
 * @PayPal
 */
public __construct(CreditCardProcessorInterface $processor, TransactionLogInterface $transactionLog){
{
}
```
You can specify parameter name with qualifier. Qualifier applied all parameters without it.

```php
/**
 * @PayPal("processor")
 */
public __construct(CreditCardProcessorInterface $processor, TransactionLogInterface $transactionLog){
{
 ....
}
```
Lastly we create a binding that uses the annotation. This uses the optional annotatedWith clause in the bind() statement:
```php
protected function configure()
{
    $this->bind(CreditCardProcessorInterface::class)
        ->annotatedWith(PayPal::class)
        ->to(PayPalCreditCardProcessor::class);
```
### @Named ##

Ray comes with a built-in binding annotation `@Named` that takes a string.

```php
use Ray\Di\Di\Inject;
use Ray\Di\Di\Named;

/**
 *  @Inject
 *  @Named("processor=checkout")
 */
public __construct(CreditCardProcessorInterface $processor)
{
...
```

To bind a specific name, pass that string using the `annotatedWith()` method.
```php
protected function configure()
{
    $this->bind(CreditCardProcessorInterface::class)
        ->annotatedWith('checkout')
        ->to(CheckoutCreditCardProcessor::class);
}
```

You need to specify in case of multiple parameter.
```php
use Ray\Di\Di\Inject;
use Ray\Di\Di\Named;

/**
 *  @Inject
 *  @Named("processor=checkout,subProcessor=backUp")
 */
public __construct(CreditCardProcessorInterface $processor, CreditCardProcessorInterface $subProcessor)
{
...
```

### Instance Bindings ##

```php
protected function configure()
{
    $this->bind(UserInterface::class)->toInstance(new User);
}
```
You can bind a type to an instance of that type. This is usually only useful for objects that don't have dependencies of their own, such as value objects:

```php
protected function configure()
{
    $this->bind()
        ->annotatedWith("login_id")
        ->toInstance('bear');
}
```

### Untargeted Bindings

You may create bindings without specifying a target. This is most useful for concrete classes. An untargetted binding informs the injector about a type, so it may prepare dependencies eagerly. Untargetted bindings have no _to_ clause, like so:

```php

protected function configure()
{
    $this->bind(MyConcreteClass::class);
    $this->bind(AnotherConcreteClass::class)->in(Scope::SINGLETON);
}
```

note: annotations is not supported Untargeted Bindings

### Constructor Bindings ##

Occasionally it's necessary to bind a type to an arbitrary constructor. This comes up when the @Inject annotation cannot be applied to the target constructor: either because it is a third party class, or because multiple constructors that participate in dependency injection. 
`Provider Binding` provide the solution to this problem. By calling your target constructor explicitly, you don't need reflection and its associated pitfalls. But there are limitations of that approach: manually constructed instances do not participate in AOP.

To address this, Ray.Di has `toConstructor` bindings.

```php
<?php
class Car
{
    public function __construct(EngineInterface $engine, $carName)
    {
        // ...
```
```php
<?php
protected function configure()
{
    $this->bind(EngineInterface::class)->annotatedWith('na')->to(NaturalAspirationEngine::class);
    $this->bind()->annotatedWith('car_name')->toInstance('Eunos Roadster');
    $this
        ->bind(CarInterface::class)
        ->toConstructor(
            Car::class,
            'engine=na,carName=car_name' // varName=BindName,...
        );
}
```
In this example, the `Car` have a constructor which name bound with `engine=na,carName=car_name`. That constructor does not need an @Inject annotation. Ray.Di will invoke that constructor to satisfy the binding.

## Scopes ##

By default, Ray returns a new instance each time it supplies a value. This behaviour is configurable via scopes.
You can also configure scopes with the `@Scope` annotation.

```php
use Ray\Di\Scope;

protected function configure()
{
    $this->bind(TransactionLogInterface::class)->to(InMemoryTransactionLog::class)->in(Scope::SINGLETON);
}
```

## Object life cycle

`@PostConstruct` is used on methods that need to get executed after dependency injection has finalized to perform any extra initialization.

```php

use Ray\Di\Di\PostConstruct;

/**
 * @PostConstruct
 */
public function init()
{
    //....
}
```
## Injection Point

An **InjectionPoint** is a class that has information about an injection point. 
It provides access to metadata via `\ReflectionParameter` or an annotation in `Provider`.

For example, the following get method of `Psr3LoggerProvider` class creates injectable Loggers. The log category of a Logger depends upon the class of the object into which it is injected.

```php
class Psr3LoggerProvider implements ProviderInterface
{
    /**
     * @var InjectionPoint
     */
    private $ip;

    public function __construct(InjectionPointInterface $ip)
    {
        $this->ip = $ip;
    }

    public function get()
    {
        $logger = new \Monolog\Logger($this->ip->getClass->getName());
        $logger->pushHandler(new StreamHandler('path/to/your.log', Logger::WARNING));

        return $logger;
    }
}
```
Obtains the qualifiers
```php
$annotations =  $this->ip->getQualifiers();
```
## Automatic Injection ##

Ray.Di automatically injects all of the following:

 * instances passed to `toInstance()` in a bind statement
 * provider instances passed to `toProvider()` in a bind statement

The objects will be injected while the injector itself is being created. If they're needed to satisfy other startup injections, Ray.Di will inject them before they're used.

## Aspect Oriented Programing ##

To compliment dependency injection, Ray.Di supports method interception. This feature enables you to write code that is executed each time a matching method is invoked. It's suited for cross cutting concerns ("aspects"), such as transactions, security and logging. Because interceptors divide a problem into aspects rather than objects, their use is called Aspect Oriented Programming (AOP).

To mark select methods as weekdays-only, we define an annotation .

```php
/**
 * NotOnWeekends
 *
 * @Annotation
 * @Target("METHOD")
 */
final class NotOnWeekends
{
}
```

...and apply it to the methods that need to be intercepted:

```php
class BillingService
{
    /**
     * @NotOnWeekends
     */
    chargeOrder(PizzaOrder $order, CreditCard $creditCard)
    {
```

Next, we define the interceptor by implementing the org.aopalliance.intercept.MethodInterceptor interface. When we need to call through to the underlying method, we do so by calling `$invocation->proceed()`:

```php

use Ray\Aop\MethodInterceptor;
use Ray\Aop\MethodInvocation;

class WeekendBlocker implements MethodInterceptor
{
    public function invoke(MethodInvocation $invocation)
    {
        $today = getdate();
        if ($today['weekday'][0] === 'S') {
            throw new \RuntimeException(
                $invocation->getMethod()->getName() . " not allowed on weekends!"
            );
        }
        return $invocation->proceed();
    }
}
```

Finally, we configure everything. In this case we match any class, but only the methods with our `@NotOnWeekends` annotation:

```php

use Ray\Di\AbstractModule;

class WeekendModule extends AbstractModule
{
    protected function configure()
    {
        $this->bindInterceptor(
            $this->matcher->any(),                           // any class
            $this->matcher->annotatedWith('NotOnWeekends'),  // @NotOnWeekends method
            [WeekendBlocker::class]                          // apply WeekendBlocker interceptor
        );
    }
}

$injector = new Injector(new WeekendModule);
$billing = $injector->getInstance(BillingServiceInterface::class);
try {
    echo $billing->chargeOrder();
} catch (\RuntimeException $e) {
    echo $e->getMessage() . "\n";
    exit(1);
}
```
Putting it all together, (and waiting until Saturday), we see the method is intercepted and our order is rejected:

```php
RuntimeException: chargeOrder not allowed on weekends! in /apps/pizza/WeekendBlocker.php on line 14

Call Stack:
    0.0022     228296   1. {main}() /apps/pizza/main.php:0
    0.0054     317424   2. Ray\Aop\Weaver->chargeOrder() /apps/pizza/main.php:14
    0.0054     317608   3. Ray\Aop\Weaver->__call() /libs/Ray.Aop/src/Weaver.php:14
    0.0055     318384   4. Ray\Aop\ReflectiveMethodInvocation->proceed() /libs/Ray.Aop/src/Weaver.php:68
    0.0056     318784   5. Ray\Aop\Sample\WeekendBlocker->invoke() /libs/Ray.Aop/src/ReflectiveMethodInvocation.php:65
```

You can bind interceptors in variouas ways as follows.

```php

use Ray\Di\AbstractModule;

class TaxModule extends AbstractModule
{
    protected function configure()
    {
        $this->bindInterceptor(
            $this->matcher->annotatedWith('Tax'),
            $this->matcher->any(),
            [TaxCharger::class]
        );
    }
}
```

```php

use Ray\Di\AbstractModule;

class AopMatcherModule extends AbstractModule
{
    protected function configure()
    {
        $this->bindInterceptor(
            $this->matcher->any(),                 // In any class and
            $this->matcher->startWith('delete'),   // ..the method start with "delete"
            [Logger::class]
        );
    }
}
```

## Installation ##

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
    $instance = $compiler->getInstance(ListerInterface::class);
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

### method 1: Cache injector
```
## Frameworks integration ##

 * [lorenzo/piping-bag](https://github.com/lorenzo/piping-bag) for CakePHP3
 * [BEAR.Sunday](https://github.com/koriym/BEAR.Sunday)

## Modules ##

Various modules for `Ray.Di` are available at https://github.com/Ray-Di.

## Requirements ##

* PHP 5.4+
* hhvm

## Installation ##

The recommended way to install Ray.Di is through [Composer](https://github.com/composer/composer).

```bash
# Add Ray.Di as a dependency
$ composer require ray/di ~2.0
```

## Testing Ray.Di ##

Here's how to install Ray.Di from source and run the unit tests and demos.

```bash
$ composer create-project ray/di:~2.0 Ray.Di
$ cd Ray.Di
$ phpunit
$ php docs/demo/run.php
```
