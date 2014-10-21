Dependency Injection framework for PHP
======================================

[![Latest Stable Version](https://poser.pugx.org/ray/di/v/stable.png)](https://packagist.org/packages/ray/di)
[![Build Status](https://secure.travis-ci.org/koriym/Ray.Di.png?branch=develop-2)](http://travis-ci.org/koriym/Ray.Di)
[![Scrutinizer Code Quality](https://scrutinizer-ci.com/b/koriym/ray.di/badges/quality-score.png?b=develop-2&s=38a2876fe3393f2d5307f3b4c6b5fb0b23812be1)](https://scrutinizer-ci.com/b/koriym/ray.di/?branch=develop-2)
[![Code Coverage](https://scrutinizer-ci.com/g/koriym/Ray.Di/badges/coverage.png?s=676589defaa2a762ac42ed97f2a7e64efc4617b9)](https://scrutinizer-ci.com/g/koriym/Ray.Di/)

**Ray.Di** was created in order to get Guice style dependency injection in PHP projects. It tries to mirror Guice's behavior and style. [Guice](http://code.google.com/p/google-guice/wiki/Motivation?tm=6) is a Java dependency injection framework developed by Google.

Getting Stated
--------------

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
This is an example of **Linked Bindings**. Linked bindings map a type to its implementation.

### Provider Bindings

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
$this->bind('TransactionLogInterface')->toProvider('DatabaseTransactionLogProvider');
```

### Named Binding

Ray comes with a built-in binding annotation `@Named` that takes a string.

```php
use Ray\Di\Di\Inject;
use Ray\Di\Di\Named;

/**
 *  @Inject
 *  @Named("processor=checkout")
 */
public RealBillingService(CreditCardProcessorInterface $processor)
{
...
```

To bind a specific name, pass that string using the `annotatedWith()` method.
```php
protected function configure()
{
    $this->bind('CreditCardProcessorInterface')->annotatedWith('checkout')->to('CheckoutCreditCardProcessor');
}
```

You need to specify in case of multiple parameter.
```php
use Ray\Di\Di\Inject;
use Ray\Di\Di\Named;

/**
 *  @Inject
 *  @Named("processor=checkout,backup=subProcessor")
 */
public RealBillingService(CreditCardProcessorInterface $processor, CreditCardProcessorInterface $subProcessor)
{
...
```

### Instance Bindings

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
    $this->bind()->annotatedWith("login_id")->toInstance('bear');
}
```

### Explicit Binding

Occasionally it's necessary to bind a type to an arbitrary constructor. This arises when the `@Inject` annotation cannot be applied to the target constructor. eg. when it is a third party class.

```php
use Ray\Di\InjectionPoints;

protected function configure()
{
    $this
        ->bind(FakeCarInterface::class)
        ->toExplicit(
            FakeCar::class,
            (new InjectionPoints)->addMethod('setTires')->addMethod('setHardtop'),
            'postConstruct'
        );
}
```

## Scopes

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

## Automatic Injection

Ray.Di automatically injects all of the following:

 * instances passed to `toInstance()` in a bind statement
 * provider instances passed to `toProvider()` in a bind statement

The objects will be injected while the injector itself is being created. If they're needed to satisfy other startup injections, Ray.Di will inject them before they're used.

## Aspect Oriented Programing

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
            $this->matcher->any(),
            $this->matcher->annotatedWith('NotOnWeekends'),
            [$this->requestInjection(WeekendBlocker::class)]
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
            [$this->requestInjection(TaxCharger::class)]
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
            [$this->requestInjection(Logger::class)]
        );
    }
}
```

## Installation

A module can install other modules to configure more bindings.

 * Earlier bindings have priority even if the same binding is made later.
 * `overrideInstall` bindings in that module have priority.

```php
protected function configure()
{
    $this->install(new OtherModule);
    $this->overrideInstall(new CustomiseModule);
}
```

## Injection in the module

You can use a built-in injector in the module which uses existing bindings.

```php
protected function configure()
{
    $this->bind(DbInterface::class)->to(Db::class);
    $dbLogger = $this->requestInjection(DbLogger::class);
}
```

Best practice
-------------
Your code should deal directly with the Injector as little as possible. Instead, you want to bootstrap your application by injecting one root object.
The class of this object should use injection to obtain references to other objects on which it depends. The classes of those objects should do the same.

Performance boost
-------------

インジェクターオブジェクトをシリアライズすると、束縛の最適化が行われます。
`unserialize`して利用したインジェクターではパフォーマンスが向上します。

```php

// save
$injector = new Injector(new ListerModule);
$cachedInjector = serialize($injector);

// load
$injector = unserialize($cachedInjector);
$lister = $injector->getInstance(ListerInterface::class);
```

Requirements
------------

* PHP 5.5+

Installation
------------

The recommended way to install Ray.Di is through [Composer](https://github.com/composer/composer).

```bash
# Add Ray.Di as a dependency
$ composer require ray/di 2.*
```

Testing Ray.Di
--------------

Here's how to install Ray.Di from source and run the unit tests and samples.

```bash
$ composer create-project ray/di Ray.Di 2.*
$ cd Ray.Di
$ phpunit
$ php tests/example/run.php
```
