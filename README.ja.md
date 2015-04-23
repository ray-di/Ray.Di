# Ray.Di

## Dependency Injection framework ##

**Ray.Di**はGoogleの[Guice](http://code.google.com/p/google-guice/wiki/Motivation?tm=6)の主要な機能を持つアノテーションベースのDIフレームワークです。

 * [AOP Alliance](http://aopalliance.sourceforge.net/)に準拠したアスペクト指向プログラミングをサポートしています。
 * [Doctrine.Commons](http://www.doctrine-project.org/projects/common)アノテーションを使用しています。

## Getting Started ##

### Linked Bindings ###

**Linked bindings** はインターフェイスとその実装クラスを束縛します。

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

### Provider Bindings ###

[Provider bindings](http://code.google.com/p/rayphp/wiki/ProviderBindings) はインターフェイスと実装クラスの**プロバイダー**を束縛します。プロバイダーは依存にインスタンスを`get`メソッドで返します。

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

プロバイダーにも依存が注入されます。
インスタンス生成にファクトリーコードが必要な時に **Provider Bindings** を使います。

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

### Binding Annotations ###

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

### @Named ###

Rayには`@Named`という文字列で`名前`を指定できるビルトインアノテーションがあります。同じインターフェイスの依存を`名前`で区別します。

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

メソッドの引数が複数の場合は`変数名=名前`のペアでカンマ区切りの文字列を指定します。

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

### Instance Bindings ###

`toInstance`は値を直接束縛します。

```php
protected function configure()
{
    $this->bind(UserInterface::class)->toInstance(new User);
}
```

定数は`@Named`を使って束縛します。

```php
protected function configure()
{
    $this->bind()
        ->annotatedWith("login_id")
        ->toInstance('bear');
}
```

### Untargeted Bindings ###

ターゲットを指定しないで束縛をつくることがで、コンクリートクラスの束縛に便利です。事前にインジェクターに型の情報を伝えるので束縛を事前に行いエラー検知や最適化を行うことができます。
Untargetted bindingsは以下のように`to()`が必要ありません。

```php
protected function configure()
{
    $this->bind(MyConcreteClass::class);
    $this->bind(AnotherConcreteClass::class)->in(Scope::SINGLETON);
}
```

note: annotations is not supported Untargeted Bindings

### Constructor Bindings ###

`@Inject`アノテーションのないサードパーティーのクラスに特定の束縛を指定するのに`toConstructor`を使うことができます。クラス名と`Named Binding`を指定して束縛します。

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

この例では`Car`クラスでは`EngineInterface $engine, $carName`と二つの引数が必要ですが、それぞれの変数名に`Named binding`束縛を行い依存解決をしています。

## Scopes ##

デフォルトでは、Rayは毎回新しいインスタンスを生成しますが、これはスコープの設定で変更することができます。

```php
use Ray\Di\Scope;

protected function configure()
{
    $this->bind(TransactionLogInterface::class)->to(InMemoryTransactionLog::class)->in(Scope::SINGLETON);
}
```

## Object Life Cycle ##

オブジェクトライフサイクルのアノテーションを使ってオブジェクトの初期化のメソッドを指定する事ができます。

このメソッドは全ての依存がインジェクトされた後に呼ばれます。
セッターインジェクションがある場合などでも全ての必要な依存が注入された前提にすることができます。

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

## Injection Point ##

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

Ray.Aopのアスペクト指向プログラミングが利用できます。

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

モジュールは他のモジュールの束縛をインストールして使う事ができます。

 * 同一の束縛があれば先にされた方が優先されます。
 * `override`でインストールすると後からのモジュールが優先されインストールされます。

```php
protected function configure()
{
    $this->install(new OtherModule);
    $this->override(new CustomiseModule);
}
```

## Best practice ##

可能な限りインジェクターを直接使わないコードにします。その代わりアプリケーションのbootstrapで **ルートオブジェクト** をインジェクトするようにします。
このルートオブジェクトのクラスは依存する他のオブジェクトのインジェクションに使われます。その先のオブジェクトも同じで、依存が依存を必要として最終的にオブジェクトグラフが作られます。

## Performance boost ##

インジェクターオブジェクトは全ての依存情報を保持していてシリアライズすることができます。
`unserialize`したインジェクターではリフレクションやアノテーションを使用しないで、高速にインジェクションを行うことができます。


```php
// save
$injector = new Injector(new ListerModule);
$cachedInjector = serialize($injector);

// load
$injector = unserialize($cachedInjector);
$lister = $injector->getInstance(ListerInterface::class);
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

Ray.Diをインストールにするには [Composer](http://getcomposer.org)を利用します。

```bash
# Add Ray.Di as a dependency
$ composer require ray/di ~2.0
```

## Testing Ray.Di ##

インストールしてテストとデモプログラムを実行するにはこのようにします。

```bash
$ composer create-project ray/di:~2.0 Ray.Di
$ cd Ray.Di
$ phpunit
$ php docs/demo/run.php
```
