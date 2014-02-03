Dependency Injection framework for PHP
======================================

[![Latest Stable Version](https://poser.pugx.org/ray/di/v/stable.png)](https://packagist.org/packages/ray/di)
[![Build Status](https://secure.travis-ci.org/koriym/Ray.Di.png?branch=master)](http://travis-ci.org/koriym/Ray.Di)

**Ray.Di** was created in order to get Guice style dependency injection in PHP projects. It tries to mirror Guice's behavior and style. [Guice]((http://code.google.com/p/google-guice/wiki/Motivation?tm=6) is a Java dependency injection framework developed by Google.

 * Supports some of the [JSR-250](http://en.wikipedia.org/wiki/JSR_250) object lifecycle annotations (`@PostConstruct`, `@PreDestroy`)
 * Provides an [AOP Alliance](http://aopalliance.sourceforge.net/)-compliant aspect-oriented programming implementation.
 * Extends [Aura.Di](http://auraphp.github.com/Aura.Di).
 * [Doctrine.Common](http://www.doctrine-project.org/projects/common) annotations.

_Not all features of Guice have been implemented._


Getting Stated
--------------

Here is a basic example of dependency injection using Ray.Di.

```php
use Ray\Di\AbstractModule;
use Ray\Di\Di\Inject;
use Ray\Di\Injector;

interface FinderInterface
{
}

class Finder implements FinderInterface
{
}

class Lister
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


class Module extends \Ray\Di\AbstractModule
{
    public function configure()
    {
        $this->bind('MovieApp\FinderInterface')->to('MovieApp\Finder');
    }
}
$injector = Injector::create([new Module]);
$lister = $injector->getInstance('MovieApp\Lister');
$works = ($lister->finder instanceof MovieApp\Finder);
echo(($works) ? 'It works!' : 'It DOES NOT work!');

// It works!
```
This is an example of **Linked Bindings**. Linked bindings map a type to its implementation.


### Provider Bindings

[Provider bindings](http://code.google.com/p/rayphp/wiki/ProviderBindings) map a type to its provider.

```php
$this->bind('TransactionLogInterface')->toProvider('DatabaseTransactionLogProvider');
```
The provider class implements Ray's Provider interface, which is a simple, general interface for supplying values:

```php
use Ray\Di\ProviderInterface;

interface ProviderInterface
{
    public function get();
}
```
Our provider implementation class has dependencies of its own, which it receives via a contructor annotated with `@Inject`.
It implements the Provider interface to define what's returned with complete type safety:

```php

use Ray\Di\Di\Inject;

class DatabaseTransactionLogProvider implements Provider
{
    private ConnectionInterface connection;

    /**
     * @Inject
     */
    public DatabaseTransactionLogProvider(ConnectionInterface $connection)
    {
        $this->connection = $connection;
    }

    public TransactionLog get()
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
 *  @Named("processor=Checkout")
 */
public RealBillingService(CreditCardProcessor $processor)
{
...
```

To bind a specific name, pass that string using the `annotatedWith()` method.
```php
protected function configure()
{
    $this->bind('CreditCardProcessorInterface')->annotatedWith('Checkout')->to('CheckoutCreditCardProcessor');
}
```

### Instance Bindings

```php
protected function configure()
{
    $this->bind('UserIntetrface')->toInstance(new User);
}
```
You can bind a type to an instance of that type. This is usually only useful for objects that don't have dependencies of their own, such as value objects:

```php
protected function configure()
{
    $this->bind()->annotatedWith("login_id")->toInstance('bear');
}
```

### Constructor Bindings

Occasionally it's necessary to bind a type to an arbitrary constructor. This arises when the `@Inject` annotation cannot be applied to the target constructor. eg. when it is a third party class.

```php
class TransactionLog
{
    public function __construct($db)
    {
     // ....
```

```php
protected function configure()
{
    $this->bind('TransactionLog')->toConstructor(['db' => new Database]);
}
```

## Scopes

By default, Ray returns a new instance each time it supplies a value. This behaviour is configurable via scopes.

```php
protected function configure()
{
    $this->bind('TransactionLog')->to('InMemoryTransactionLog')->in(Scope::SINGLETON);
}
```

## Object life cycle

`@PostConstruct` is used on methods that need to get executed after dependency injection has finalized to perform any extra initialization.

```php

use Ray\Di\Di\PostConstruct;

/**
 * @PostConstruct
 */
public function onInit()
{
    //....
}
```

`@PreDestroy` is used on methods that are called after script execution finishes or exit() is called.
This method is registered by using **register_shutdown_function**.

```php

use Ray\Di\Di\PreDestroy;

/**
 * @PreDestroy
 */
public function onShutdown()
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
            [new WeekendBlocker]
        );
    }
}

$injector = Injector::create([new WeekendModule]);
$billing = $injector->getInstance('BillingService');
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
            [new TaxCharger]
        );
    }
}
```

```php

use Ray\Di\AbstractModule;

class AopMatcherModule extends AbstractModule
{
    pro
    protected function configure()
    {
        $this->bindInterceptor(
            $this->matcher->any(),                 // In any class and
            $this->matcher->startWith('delete'), // ..the method start with "delete"
            [new Logger]
        );
    }
}
```

## Installation

A module can install other modules to configure more bindings.

 * Earlier bindings have priority even if the same binding is made later.
 * The module can use an existing bindings by passing in `$this`. The bindings in that module have priority.

```php
protected function configure()
{
    $this->install(new OtherModule);
    $this->install(new CustomiseModule($this);
}
```

## Injection in the module

You can use a built-in injector in the module which uses existing bindings.

```php
protected function configure()
{
    $this->bind('DbInterface')->to('Db');
    $dbLogger = $this->requestInjection('DbLogger');
}
```

Best practice
-------------
Your code should deal directly with the Injector as little as possible. Instead, you want to bootstrap your application by injecting one root object.
The class of this object should use injection to obtain references to other objects on which it depends. The classes of those objects should do the same.

Caching dependency-injected objects
-----------------------------------

Storing dependency-injected objects in a cache container has huge performance boosts.
**CacheInjector** also handles *object life cycle* as well as auto loading of generated aspect weaved objects.

```php
$injector = function()  {
    return Injector::create([new AppModule]);
};
$initialization = function() {
    // initialize per system startup (not per each request)
};
$injector = new CacheInjector($injector, $initialization, 'cache-namespace', new ApcCache);
$app = $injector->getInsntance('ApplicationInterface');
$app->run();
```

Cachealbe class example
-----------------------

```php

use Ray\Di\Di\Inject;
use Ray\Di\Di\PostConstruct;

class UserRepository
{
    private $dependency;

    /**
     * @Inject
     */
    public function __construct(DependencyInterface $dependency)
    {
        // per system startup
        $this->dependency = $dependency;
    }

    /**
     * @PostConstruct
     */
    public function init()
    {
        // per each request
        //
        // In this @PostConstruct method, You can expect
        // - All injection is completed.
        // - This function is called regardless object cache status unlike __construct or __wakeup.
        // - You can set unserializable item to property such as closure or \PDO object.
    }

    public function getUserData($Id)
    {
        // The request is stateless.
    }
}
```

Requirements
------------

* PHP 5.4+

Documentation
-------------

Available at Google Code.

 [http://code.google.com/p/rayphp/wiki/Motivation?tm=6](http://code.google.com/p/rayphp/wiki/Motivation?tm=6)


Installation
------------

The recommended way to install Ray.Di is through [Composer](https://github.com/composer/composer).

```bash
# Install Composer
$ curl -sS https://getcomposer.org/installer | php

# Add Ray.Di as a dependency
$ php composer.phar require ray/di:*
```

Testing Ray.Di
--------------

Here's how to install Ray.Di from source and run the unit tests and samples.

```bash
$ git clone git://github.com/koriym/Ray.Di.git
$ cd Ray.Di
$ composer install
$ phpunit
$ php doc/sample/00-newsletter.php
$ php doc/sample/01-db/main.php
$ cd doc/zf2-di-tests-clone/
$ php runall.php
```
