# Ray.Di

## Dependency Injection framework #

[![Scrutinizer Code Quality](https://scrutinizer-ci.com/g/ray-di/Ray.Di/badges/quality-score.png?b=2.x)](https://scrutinizer-ci.com/g/ray-di/Ray.Di/?branch=2.x)
[![codecov](https://codecov.io/gh/ray-di/Ray.Di/branch/2.x/graph/badge.svg?token=KCQXtu01zc)](https://codecov.io/gh/ray-di/Ray.Di)
[![Type Coverage](https://shepherd.dev/github/ray-di/Ray.Di/coverage.svg)](https://shepherd.dev/github/ray-di/Ray.Di)
[![Build Status](https://scrutinizer-ci.com/g/ray-di/Ray.Di/badges/build.png?b=2.x)](https://scrutinizer-ci.com/g/ray-di/Ray.Di/build-status/2.x)
[![GitHub Workflow Status (branch)](https://img.shields.io/github/workflow/status/ray-di/Ray.Di/Continuous%20Integration/2.x)](https://github.com/ray-di/Ray.Di/actions)
[![Total Downloads](https://poser.pugx.org/ray/di/downloads)](https://packagist.org/packages/ray/di)

**Ray.Di** was created in order to get [Guice](https://github.com/google/guice/wiki) style dependency injection in PHP projects. It tries to mirror Guice's behavior and style. 

## Overview

The Ray.Di package provides a dependency injector
with the following features:

- Constructor injection and setter injection
- Automatic injection 
- Post-construct initialization
- Raw PHP factory code compiler
- Binding annotations / attributes
- Contextual injection with injection point meta data
- Aspect oriented programming (AOP)
- Assisted injection

# Getting Started

## Creating Object graph 

With dependency injection, objects accept dependencies in their constructors. To construct an object, you first build its dependencies. But to build each dependency, you need its dependencies, and so on. So when you build an object, you really need to build an object graph.

Building object graphs by hand is labour intensive, error prone, and makes testing difficult. Instead, Ray.Di can build the object graph for you. But first, Ray.Di needs to be configured to build the graph exactly as you want it.

To illustrate, we'll start the BillingService class that accepts its dependent interfaces in its constructor: ProcessorInterface and LoggerInterface.

```php
class BillingService
{
    private ProcessorInterface $processor;
    private LoggerInterface $logger;
    
    public function __construct(ProcessorInterface $processor, LoggerInterface $logger)
    {
        $this->processor = $processor;
        $this->logger = $logger;
    }
}
```

Ray.Di uses bindings to map types to their implementations. A module is a collection of bindings specified using fluent, English-like method calls:

```php
class BillingModule extends AbstractModule
{
    protected function configure()
    {
        $this->bind(ProcessorInterface::class)->to(PaypalProcessor::class); 
        $this->bind(LoggerInterface::class)->to(DatabaseLogger::class);
    }
}
```

The modules are the building blocks of an injector, which is Ray.Di's object-graph builder. First we create the injector, and then we can use that to build the BillingService:

```php
$injector = new Injector(new BillingModule);
$billingService = $injector->getInstance(BillingService::class);
```

By building the billingService, we've constructed a small object graph using Ray.Di. 

# Injections

## Constructor Injection

Constructor injection combines instantiation with injection. This constructor should accept class dependencies as parameters. Most constructors will then assign the parameters to properties. You do not need `#[Inject]` attribute in constructor.

```php
public function __construct(DbInterface $db)
{
    $this->db = $db;
}
```
    
## Setter Injection

Ray.Di can inject methods that have the `#[Inject]` attribute. Dependencies take the form of parameters, which the injector resolves before invoking the method. Injected methods may have any number of parameters, and the method name does not impact injection.

```php
use Ray\Di\Di\Inject;
```

```php
#[Inject]
public function setDb(DbInterface $db)
{
    $this->db = $db;
}
```

## Property Injection

Ray.Di does not support property injection.

## Assisted Injection

It is also possible to inject dependencies directly in the invoke method parameter(s). When doing this, add the dependency to the end of the arguments and add `#[Assisted]` to the parameter(s). You need `null` default for that parameter.

```php
use Ray\Di\Di\Assisted;
```

```php
public function doSomething($id, #[Assisted] DbInterface $db = null)
{
    $this->db = $db;
}
```

You can also provide dependency which depends on other dynamic parameter in method invocation. `MethodInvocationProvider` provides [MethodInvocation](https://github.com/ray-di/Ray.Aop/blob/2.x/src/MethodInvocation.php) object.

```php
class HorizontalScaleDbProvider implements ProviderInterface
{
    public function __construct(
        private MethodInvocationProvider $invocationProvider)
    ){}

    public function get()
    {
        $methodInvocation = $this->invocationProvider->get();
        [$id] = $methodInvocation->getArguments()->getArrayCopy();
        
        return UserDb::withId($id); // $id for database choice.
    }
}
```

# Bindings

The injector's job is to assemble graphs of objects. You request an instance of a given type, and it figures out what to build, resolves dependencies, and wires everything together. To specify how dependencies are resolved, configure your injector with bindings.

## Creating Bindings

To create bindings, extend AbstractModule and override its configure method. In the method body, call bind() to specify each binding. These methods are type checked in compile can report errors if you use the wrong types. Once you've created your modules, pass them as arguments to **Injector** to build an injector.

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

## Linked Bindings

Linked bindings map a type-hint to its implementation. This example maps the interface TransactionLogInterface to the implementation DatabaseTransactionLog:

```php
class BillingModule extends AbstractModule
{
    protected function configure()
    {
        $this->bind(TransactionLogInterface::class)->to(DatabaseTransactionLog::class);
    }
}
```

## Provider Bindings ##

Provider bindings map a type-hint to its provider.

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
Our provider implementation class has dependencies of its own, which it receives via a contructor.
It implements the Provider interface to define what's returned with complete type safety:

```php

use Ray\Di\Di\Inject;
use Ray\Di\ProviderInterface;

class DatabaseTransactionLogProvider implements ProviderInterface
{
    public function __construct(
        private ConnectionInterface $connection)
    ){}

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


## Injection Point

An **InjectionPoint** is a class that has information about an injection point. 
It provides access to metadata via `\ReflectionParameter` or an attribute in `Provider`.

For example, the following `get()` method of `Psr3LoggerProvider` class creates injectable Loggers. The log category of a Logger depends upon the class of the object into which it is injected.

```php
class Psr3LoggerProvider implements ProviderInterface
{
    public function __construct(
        private InjectionPointInterface $ip
    ){}

    public function get()
    {
        $logger = new \Monolog\Logger($this->ip->getClass()->getName());
        $logger->pushHandler(new StreamHandler('path/to/your.log', Logger::WARNING));

        return $logger;
    }
}
```
`InjectionPointInterface` provides following methods. 

```php
$ip->getClass();      // \ReflectionClass
$ip->getMethod();     // \ReflectionMethod
$ip->getParameter();  // \ReflectionParameter
$ip->getQualifiers(); // (array) $qualifierAnnotations
```

## Instance Bindings

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

## Untargeted Bindings

You may create bindings without specifying a target. This is most useful for concrete classes. An untargetted binding informs the injector about a type, so it may prepare dependencies eagerly. Untargetted bindings have no _to_ clause, like so:

```php

protected function configure()
{
    $this->bind(MyConcreteClass::class);
    $this->bind(AnotherConcreteClass::class)->in(Scope::SINGLETON);
}
```

note: annotations are not supported for Untargeted Bindings

## Binding Attributes ##

Occasionally you'll want multiple bindings for a same type. For example, you might want both a PayPal credit card processor and a Google Checkout processor.
To enable this, bindings support an optional binding attribute. The attribute and type together uniquely identify a binding. This pair is called a key.

Define qualifier attribute first. It needs to be annotated with `Qualifier` attribute.

```php
use Ray\Di\Di\Qualifier;

#[Attribute(Attribute::TARGET_METHOD), Qualifier]
final class PayPal
{
}
```

To depend on the annotated binding, apply the attribute to the injected parameter:

```php
public function __construct(#[Paypal] CreditCardProcessorInterface $processor)
{
}
```
You can specify parameter name with qualifier. Qualifier applied all parameters without it.

```php
public function __construct(#[Paypal('processor')] CreditCardProcessorInterface $processor)
{
 ....
}
```
Lastly we create a binding that uses the attribute. This uses the optional `annotatedWith` clause in the bind() statement:

```php
protected function configure()
{
    $this->bind(CreditCardProcessorInterface::class)
        ->annotatedWith(PayPal::class)
        ->to(PayPalCreditCardProcessor::class);
```

By default your custom `#[Attribute]` annotations will only help injecting dependencies in constructors on when
you annotate you also annotate your methods with `#[Inject]`.

### Binding Annotations in Setters ###

In order to make your custom `Qualifier` attribute inject dependencies by default in any method the
attribute is added, you need to implement the `Ray\Di\Di\InjectInterface`:

```php
use Ray\Di\Di\InjectInterface;
use Ray\Di\Di\Qualifier;

#[Attribute(Attribute::TARGET_METHOD), Qualifier]
final class PaymentProcessorInject implements InjectInterface
{
    public function isOptional()
    {
        return $this->optional;
    }
    
    public function __construct(
        public bool $optional = true
        public string $type;
    ){}
}
```

The interface requires that you implement the `isOptional()` method. It will be used to determine whether
or not the injection should be performed based on whether there is a known binding for it.

Now that you have created your custom injector attribute, you can use it on any method.

```php
#[PaymentProcessorInject(type: 'paypal')]
public setPaymentProcessor(CreditCardProcessorInterface $processor)
{
 ....
}
```

Finally, you can bind the interface to an implementation by using your new annotated information:

```php
protected function configure()
{
    $this->bind(CreditCardProcessorInterface::class)
        ->annotatedWith(PaymentProcessorInject::class)
        ->toProvider(PaymentProcessorProvider::class);
}
```

The provider can now use the information supplied in the qualifier attribute in order to instantiate
the most appropriate class.

### Qualifier ###

The most common use of a Qualifier attribute is tagging arguments in a function with a certain label,
the label can be used in the bindings in order to select the right class to be instantiated. For those
cases, Ray.Di comes with a built-in binding attribute `#[Named]` that takes a string.

```php
use Ray\Di\Di\Inject;
use Ray\Di\Di\Named;

public function __construct(#[Named('checkout')] CreditCardProcessorInterface $processor)
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

You need to put the `#[Named()]' attribuet in order to specify the parameter.

```php
use Ray\Di\Di\Inject;
use Ray\Di\Di\Named;

public function __construct(#[Named('checkout')] CreditCardProcessorInterface $processor, #[Named('backup')] CreditCardProcessorInterface $subProcessor)
{
...
```

## Constructor Bindings ##

When `#[Inject]` attribute cannot be applied to the target constructor or setter method because it is a third party class, Or you simply don't like to use annotations. `Constructor Binding` provide the solution to this problem. By calling your target constructor explicitly, you don't need reflection and its associated pitfalls. But there are limitations of that approach: manually constructed instances do not participate in AOP.

To address this, Ray.Di has `toConstructor` bindings.

```php
<?php
class WebApi implements WebApiInterface
{
    private $id;
    private $password;
    private $client;
    private $token;

    public function __construct(#[Named('user_id')] string $id, #[Named('user_password')] string $password)
    {
        $this->id = $id;
        $this->password = $password;
    }
    
    #[Inject]
    public function setGuzzle(ClientInterface $client)
    {
        $this->client = $client;
    }

    #[Inject(optional: true)] 
    public function setOptionalToken(#[Named('token') string $token)
    {
        $this->token = $token;
    }

    #[PostConstruct]
    public function initialize()
    {
    }
```

All attributes in dependent above can be removed by following `toConstructor` binding.

```php
<?php
protected function configure()
{
    $this
        ->bind(WebApiInterface::class)
        ->toConstructor(
            WebApi::class,                              // string $class_name
            [
                'id' => 'user_id',                    // array $name
                'passowrd' => 'user_password'
            ],
            (new InjectionPoints)                       // InjectionPoints　$setter_injection
                ->addMethod('setGuzzle', 'token')
                ->addOptionalMethod('setOptionalToken'),
            'initialize'                                // string $postCostruct
        );
    $this->bind()->annotated('user_id')->toInstance($_ENV['user_id']);
    $this->bind()->annotated('user_password')->toInstance($_ENV['user_password']);
}
```

### Parameter

**class_name**

Class Name

**name**

Parameter name binding. 

array `[[$parame_name => $binding_name],...]` or string `"param_name=binding_name&..."`

**setter_injection**

Setter Injection

**postCosntruct**
 
Ray.Di will invoke that constructor and setter method to satisfy the binding and invoke in `$postCosntruct` method after all dependencies are injected.

### PDO Example

Here is the example for the native [PDO](http://php.net/manual/ja/pdo.construct.php) class. 

```php
public PDO::__construct ( string $dsn [, string $username [, string $password [, array $options ]]] )
```

```php
protected function configure()
{       
    $this->bind(\PDO::class)->toConstructor(
        \PDO::class,
        [
            'dsn' => 'pdo_dsn',
            'username' => 'pdo_username',
            'password' => 'pdo_password'
        ]
    )->in(Scope::SINGLETON);
    $this->bind()->annotatedWith('pdo_dsn')->toInstance($dsn);
    $this->bind()->annotatedWith('pdo_username')->toInstance(getenv('db_user'));
    $this->bind()->annotatedWith('pdo_password')->toInstance(getenv('db_password'));
}
```

Since no argument of PDO has a type, it binds with the `Name Binding` of the second argument of the `toConstructor()` method.

## Null Object Binding

A Null Object is an object that implements an interface but does not do anything in its methods.
When bound with toNull(), the code of the Null Object is generated from the interface and bound to the generated instance.
This is useful for testing and AOP.

```php
protected function configure()
{
    $this->bind(CreditCardProcessorInterface::class)->toNull();
}
```

## Scopes ##

By default, Ray returns a new instance each time it supplies a value. This behaviour is configurable via scopes.

```php
use Ray\Di\Scope;

protected function configure()
{
    $this->bind(TransactionLogInterface::class)->to(InMemoryTransactionLog::class)->in(Scope::SINGLETON);
}
```

## Object life cycle

`#[PostConstruct]` is used on methods that need to get executed after dependency injection has finalized to perform any extra initialization.

```php

use Ray\Di\Di\PostConstruct;

#[PostConstruct]
public function init()
{
    //....
}
```

## Aspect Oriented Programing ##

To compliment dependency injection, Ray.Di supports method interception. This feature enables you to write code that is executed each time a matching method is invoked. It's suited for cross cutting concerns ("aspects"), such as transactions, security and logging. Because interceptors divide a problem into aspects rather than objects, their use is called Aspect Oriented Programming (AOP).

To mark select methods as weekdays-only, we define an attribute.

```php
#[Attribute(Attribute::TARGET_METHOD)]
final class NotOnWeekends
{
}
```

...and apply it to the methods that need to be intercepted:

```php
class BillingService
{
    #[NotOnWeekends]
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

Finally, we configure everything. In this case we match any class, but only the methods with our `#[NotOnWeekends]` attribute:

```php

use Ray\Di\AbstractModule;

class WeekendModule extends AbstractModule
{
    protected function configure()
    {
        $this->bindInterceptor(
            $this->matcher->any(),                           // any class
            $this->matcher->annotatedWith('NotOnWeekends'),  // #[NotOnWeekends] attributed method
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

## More Docs

 * [Contextual binding](docs/contextual_binding.md)
 * [Performance Boost](docs/performance_boost.md)
 * [Grapher](docs/grapher.md)

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
## Frameworks integration ##

 * [BEAR.Sunday](https://github.com/koriym/BEAR.Sunday)
 * [CakePHP 3/4 PipingBag](https://github.com/lorenzo/piping-bag) by [@jose_zap](https://twitter.com/jose_zap)
 * [Yii 1](https://github.com/koriym/Ray.Dyii)

## Annotation / Attribute

Ray.Di can be used either with [doctrine/annotation](https://github.com/doctrine/annotations) in PHP 7/8 or with an [Attributes](https://www.php.net/manual/en/language.attributes.overview.php) in PHP8.
See the annotation code examples in the older [README(v2.10)](https://github.com/ray-di/Ray.Di/tree/2.10.5/README.md).
To make forward-compatible annotations for attributes, see [Custom Annotation Classes](https://github.com/kerveros12v/sacinta4/blob/e976c143b3b7d42497334e76c00fdf38717af98e/vendor/doctrine/annotations/docs/en/custom.rst#optional-constructors-with-named-parameters).

## Installation ##

The recommended way to install Ray.Di is through [Composer](https://github.com/composer/composer).

```bash
# Add Ray.Di as a dependency
$ composer require ray/di ^2.0
```

## Testing Ray.Di ##

Here's how to install Ray.Di from source and run the unit tests and demos.

```bash
$ git clone https://github.com/ray-di/Ray.Di.git
$ cd Ray.Di
$ ./vendor/bin/phpunit
$ php demo-php8/run.php
```
