# Dependency Injection framework #

**Ray.Di**はGoogleの[Guice](http://code.google.com/p/google-guice/wiki/Motivation?tm=6)の主要な機能を持つアノテーションベースのDIフレームワークです。

 * [AOP Alliance](http://aopalliance.sourceforge.net/)に準拠したアスペクト指向プログラミングをサポートしています。
 * [Doctrine.Commons](http://www.doctrine-project.org/projects/common)アノテーションを使用しています。

# Getting Started #

## Linked Bindings ##

**Linked Bindings** はインターフェイスとその実装クラスを束縛します。

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

Linked Bindings はチェーンさせることができます。

## Provider Bindings ##

[Provider bindings](http://code.google.com/p/rayphp/wiki/ProviderBindings) はインターフェイスと実装クラスの**プロバイダー**を束縛します。

プロバイダーは依存のインスタンスを`get`メソッドで返します。

```php
use Ray\Di\ProviderInterface;

interface ProviderInterface
{
    public function get();
}
```

プロバイダーにも依存が注入されます。
インスタンス生成にファクトリーコードが必要な時に **Provider Bindings**を使います。

```php
class DatabaseTransactionLogProvider implements Provider
{
    private $connection;

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

このようにして `toProvider()` メソッドを使ってプロバイダに束縛します。

```php
$this->bind(TransactionLogInterface::class)->toProvider(DatabaseTransactionLogProvider:class);
```


## Named Binding ##

Rayには`@Named`という文字列で`名前`を指定できるビルトインアノテーションがあります。同じインターフェイスの依存を`名前`で区別します。

メソッドの引数が１つの場合
```php
/**
 *  @Inject
 *  @Named("checkout")
 */
public RealBillingService(CreditCardProcessorInterface $processor)
{
...
```

メソッドの引数が複数の場合は`変数名=名前`のペアでカンマ区切りの文字列を指定します。
```php
/**
 *  @Inject
 *  @Named("processonr=checkout,subProcessor=backup")
 */
public RealBillingService(CreditCardProcessorInterface $processor, CreditCardProcessorInterface $subProcessor)
{
...
```

名前を使って束縛するために`annotatedWith()`メソッドを使います。

```php
protected function configure()
{
    $this->bind(CreditCardProcessorInterface::class)->annotatedWith('checkout')->to(CheckoutCreditCardProcessor::class)
}
```

## Instance Bindings ##

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
    $this->bind()->annotatedWith("login_id")->toInstance('bear');
}
```

## Untargeted Bindings ##

ターゲットを指定しないで束縛をつくることができ、コンクリートクラスの束縛に便利です。事前にインジェクターに型の情報を伝えるので束縛を事前に行いエラー検知や最適化を行うことができます。
Untargetted bindingsは以下のように`to()`が必要ありません。

```php

protected function configure()
{
    $this->bind(MyConcreteClass::class);
    $this->bind(AnotherConcreteClass::class)->in(Scope::SINGLETON);
}
```

## Constructor Bindings ##

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
protected function configure()
{
    $this->bind(TransactionLog::class)->to(InMemoryTransactionLog::class)->in(Scope::SINGLETON);
}
```

## Object Life Cycle ##

オブジェクトライフサイクルのアノテーションを使ってオブジェクトの初期化のメソッドを指定する事ができます。

このメソッドは全ての依存がインジェクトされた後に呼ばれます。
セッターインジェクションがある場合などでも全ての必要な依存が注入された前提にすることができます。

```php
/**
 * @PostConstruct
 */
public function onInit()
{
    //....
}
```
## Install ##

モジュールは他のモジュールの束縛をインストールして使う事ができます。

 * 同一の束縛があれば先にされた方が優先されますが`overrinde`でインストールすると後からのモジュールが優先されインストールされます。

```php
protected function configure()
{
    $this->install(new OtherModule);
    $this->override(new CustomiseModule);
}
```

## Aspect Oriented Programing ##

Ray.Aopのアスペクト指向プログラミングが利用できます。

```php
class TaxModule extends AbstractModule
{
    protected function configure()
    {
        $this->bindInterceptor(
            $this->matcher->subclassesOf(RealBillingService::class),
            $this->matcher->annotatedWith('Tax'),
            [TaxCharger::class]
        );
    }
}
```

```php
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

# Best practice

可能な限りインジェクターを直接使わないコードにします。その代わりアプリケーションのbootstrapで **ルートオブジェクト** をインジェクトするようにします。
このルートオブジェクトのクラスは依存する他のオブジェクトのインジェクションに使われます。その先のオブジェクトも同じで、依存が依存を必要として最終的にオブジェクトグラフが作られます。

## Performance boost ##

### Script injector

`ScriptInjector` はパフォーマンス改善のためにファクトリーコードそれ自体を生成し、インスタンス生成の方法を分かりやすくします。
 
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

インスタンスが生成されれば、生成されたファクトリーファイルを `$tmpDir` で見ることができるようになります。

### Cache injector

シリアライズをして高速にインジェクションを行うようにすることもできます。

```php

// save
$injector = new Injector(new ListerModule);
$cachedInjector = serialize($injector);

// load
$injector = unserialize($cachedInjector);
$lister = $injector->getInstance(ListerInterface::class);
```

## Frameworks integration ##

 * [CakePHP3 PipingBag](https://github.com/lorenzo/piping-bag) by [@jose_zap](https://twitter.com/jose_zap)
 * [Symfony QckRayDiBundle](https://github.com/qckanemoto/QckRayDiBundle) and [sample project](https://github.com/qckanemoto/symfony-raydi-sample) by [@qckanemoto](https://twitter.com/qckanemoto)
 * [Radar](https://github.com/ray-di/Ray.Adr)
 * [BEAR.Sunday](https://github.com/koriym/BEAR.Sunday)
## Modules ##

`Ray.Di` のためのさまざまなモジュールが利用可能となっています。 https://github.com/Ray-Di

## Requirement ##

* PHP 5.6+
* hhvm


## Installation ##

Ray.Diをインストールにするには [Composer](http://getcomposer.org)を利用します。

```bash
# Add Ray.Di as a dependency
$ composer require ray/di ~2.0@dev
```

## Testing Ray.Di ##

インストールしてテストとデモプログラムを実行するにはこのようにします。

```bash
$ composer create-project ray/di Ray.Di ~2.0@dev
$ cd Ray.Di
$ phpunit
$ php docs/demo/run.php
```
