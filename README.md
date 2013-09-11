Dependency Injection framework for PHP
======================================

[![Latest Stable Version](https://poser.pugx.org/ray/di/v/stable.png)](https://packagist.org/packages/ray/di)
[![Build Status](https://secure.travis-ci.org/koriym/Ray.Di.png?branch=master)](http://travis-ci.org/koriym/Ray.Di)

This project was created in order to get Guice style dependency injection in PHP projects. It tries to mirror Guice's behavior and style. [Guice]((http://code.google.com/p/google-guice/wiki/Motivation?tm=6) is a Java dependency injection framework developed by Google. 

 * Supports some of the JSR-330 object lifecycle annotations (@PostConstruct, @PreDestroy)
 * Provides an AOP Alliance-compliant aspect-oriented programming implementation.
 * [Aura.Di](http://auraphp.github.com/Aura.Di ) extended.
 * [Doctrine.Commons](http://www.doctrine-project.org/projects/common) annotation.

_Not all features of Guice have been implemented._


Getting Stated
--------------

Here is the basic example of dependency injection by Ray.Di.

```php
use Ray\Di\Injector;
use Ray\Di\AbstractModule;

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
This is  **Linked Bindings**. Linked bindings map a type to its implementation.


### Provider Bindings

[Provider bindings](http://code.google.com/p/rayphp/wiki/ProviderBindings) map a type to its provider.

```php
$this->bind('TransactionLogInterface')->toProvider('DatabaseTransactionLogProvider');
```


### Named Binding

Ray comes with a built-in binding annotation @Named that uses a string.

```php
/**
 *  @Inject
 *  @Named("processor=Checkout") 
 */
public RealBillingService(CreditCardProcessor $processor)
{
...
```

To bind a specific name, pass specific string to annotatedWith() method.
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
You can bind a type to a specific instance of that type. This is usually only useful only for objects that don't have dependencies of their own, such as value objects:

```php
protected function configure()
{
    $this->bind()->annotatedWith("login_id")->toInstance('bear');
}
```

### Constructor Binfings

Occasionally it's necessary to bind a type to an arbitrary constructor. This comes up when the @Inject annotation cannot be applied to the target constructor: either because it is a third party class

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

This method called after all dependencies are injected in this class.

```php
/**
 * @PostConstruct
 */
public function onInit()
{
    //....
}
```

This method registered by  *register_shutdown_function , 
 executed after script execution finishes or exit() is called.

```php
/**
 * @PreDestoroy
 */
public function onShutdown()
{
    //....
}
```

## Automatic Injection

Ray.Di automatically injects all of the following:

 * instances passed to toInstance() in a bind statement
 * provider instances passed to toProvider() in a bind statement 

The objects will be injected while the injector itself is being created. If they're needed to satisfy other startup injections, Ray.Di will inject them before they're used. 

## Asepct Oritented Programing

To mark select methods as weekdays-only, we define an annotation .

```php
<?php
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
<?php
class BillingService
{
    /**
     * @NotOnWeekends
     */
    chargeOrder(PizzaOrder $order, CreditCard $creditCard)
    {
```

Next, we define the interceptor by implementing the org.aopalliance.intercept.MethodInterceptor interface. When we need to call through to the underlying method, we do so by calling $invocation->proceed():

```php
<?php
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

Finally, we configure everything.In this case we match any class, but only the methods with our @NotOnWeekends annotation:

```php

class WeekendModule extends AbstractModule
{
    public function configure()
    {
        $this->bind()->annotatedWith('NotOnWeekends')->toInterceptor(new WeekendBlocker);
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
<?php
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
public function configure()
{
    $this->matcher
      ->any()                       // In any class,
      ->startWith('delete')         // bind method start with "delete"
      ->toInterceptor(new Logger);
}
```

```php
public function configure()
{
    // In any method in CreditCardTransaction class
    $this->matcher
      ->subClassOf('CreditCardTransaction')  
      ->any()                               
      ->toInterceptor(new Logger);
}

```

Best practice
-------------
Your code should deal directly with the Injector as little as possible. Instead, you want to bootstrap your application by injecting one root object.
The class of this object should use injection to obtain references to other objects on which it depends. The classes of those objects should do the same.

Caching dependency-injected objects 
-----------------------------------

Storing dependency-injected objects in cache container gets huge performance boosts.
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

Requirement
-----------

* PHP 5.4+

Documentation
=============

Available at Google Code.

 [http://code.google.com/p/rayphp/wiki/Motivation?tm=6](http://code.google.com/p/rayphp/wiki/Motivation?tm=6)


Installation
============

The recommended way to install Ray.Di is through [Composer](https://github.com/composer/composer).

```bash
# Install Composer
$ curl -sS https://getcomposer.org/installer | php

# Add Ray.Di as a dependency
$ php composer.phar require ray/di:*
```

Testing Ray.Di
==============

Here's how to install Ray.Di from source to run the unit tests and samples.

```
$ git clone git://github.com/koriym/Ray.Di.git
$ cd Ray.Di
$ composer install
$ phpunit
$ php doc/sample/00-newsletter.php
$ php doc/sample/01-db/main.php
$ cd doc/zf2-di-tests-clone/
$ php runall.php
```

