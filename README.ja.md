Dependency Injection framework for PHP
======================================

[![Latest Stable Version](https://poser.pugx.org/ray/di/v/stable.png)](https://packagist.org/packages/ray/di)
[![Build Status](https://secure.travis-ci.org/koriym/Ray.Di.png?branch=master)](http://travis-ci.org/koriym/Ray.Di)

**Ray.Di**はGoogleのJava用DI framework [Guice](http://code.google.com/p/google-guice/wiki/Motivation?tm=6)の主要な機能を持つアノテーションベースのDIフレームワークです。
DIを効率よく使用すると以下のようなメリットがあります。

* ロジックとコンフィギュレーションの分離を促進し、ソースコードを読みやすくします。
* コンポーネントの独立性と再利用性を強化します。コンポーネントは依存関係のあるインタフェースを宣言するだけになるため、他のコンポーネントとの関係を疎結合にし再利用性を高めます。
* コーディング量を減少させます。インジェクションの処理そのものはインジェクターが提供するためその分だけ実装するコードの量が減ります。多くの場合、依存を受け取る為のtraitの`use`文を記述するだけです。

Ray.Diは以下の特徴があります。

 * [JSR-250](http://en.wikipedia.org/wiki/JSR_250)のオブジェクトライフサイクル(`@PostConstruct`, `@PreDestroy`)のアノテーションをサポートしています。
 * [AOP Alliance](http://aopalliance.sourceforge.net/)に準拠したアスペクト指向プログラミングをサポートしています。
 * [Aura.Di](http://auraphp.github.com/Aura.Di )を拡張しています。
 * [Doctrine.Commons](http://www.doctrine-project.org/projects/common)アノテーションを使用しています。

Getting Started
--------------

Ray.Diを使ったディペンデンシーインジェクション（[依存性の注入](http://ja.wikipedia.org/wiki/%E4%BE%9D%E5%AD%98%E6%80%A7%E3%81%AE%E6%B3%A8%E5%85%A5)）の一般的な例です。
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
これは **Linked Bindings** という束縛（バインディング）です。. Linked bindings はインターフェイスとその実装クラスを束縛します。

### Provider Bindings

[Provider bindings](http://code.google.com/p/rayphp/wiki/ProviderBindings) はインターフェイスと実装クラスの`プロバイダー`を束縛します。`プロバイダー`は必要とされる依存（インスタンス）を`get`メソッドで返します。

```php
use Ray\Di\ProviderInterface;

interface ProviderInterface
{
    public function get();
}
```

このプロバイダーの実装は自身にコンストラクターで`@Inject`とアノテートしている依存があります。
依存を使ってインスタンスを生成して`get()`メソッドで生成したインスタンスを返します。

```php
class DatabaseTransactionLogProvider implements Provider
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
このように依存が必要なインスタンスには **Provider Bindings**を使います。

```php
$this->bind('TransactionLogInterface')->toProvider('DatabaseTransactionLogProvider');
```



### Named Binding

Rayには`@Named`という文字列で`名前`を指定できるビルトインアノテーションがあります。同じインターフェイスの依存を`名前`で区別します。

依存が１つの場合
```php
/**
 *  @Inject
 *  @Named("checkout") 
 */
public RealBillingService(CreditCardProcessorInterface $processor)
{
...
```

同一メソッドで依存が複数ある場合は変数名を指定します。
```php
/**
 *  @Inject
 *  @Named("processonr=checkout,subProcessor=backup") 
 */
public RealBillingService(CreditCardProcessorInterface $processor, CreditCardProcessorInterface $subProcessor)
{
...
```

特定の名前を使って束縛するために`annotatedWith()`メソッドを使います。

```php
protected function configure()
{
    $this->bind('CreditCardProcessorInterface')->annotatedWith('checkout')->to('CheckoutCreditCardProcessor')    $this->bind('CreditCardProcessorInterface')->annotatedWith('backup')->to('CheckoutBackupCreditCardProcessor');
}
```

### Instance Bindings

値を直接束縛することができます。依存のないオブジェクトや配列やスカラー値などの時だけ利用するようにします。

```php
protected function configure()
{
    $this->bind('UserInterface')->toInstance(new User);
}
```

PHPのスカラー値には型がないので、名前を使って束縛します。

```php
protected function configure()
{
    $this->bind()->annotatedWith("login_id")->toInstance('bear');
}
```

### Constructor Bindings

外部のクラスなどで`@Inject`が使えない場合などに、任意のコンストラクタに型を束縛することができます。

```php
class TransactionLog
{
    public function __construct($db)
    {
     // ....
```

変数名を指定して束縛します。

```php
protected function configure()
{
    $this->bind('TransactionLog')->toConstructor(['db' => new Database]);
}
```

## Scopes

デフォルトでは、Rayは毎回新しいインスタンスを生成しますが、これはスコープの設定で変更することができます。

```php
protected function configure()
{
    $this->bind('TransactionLog')->to('InMemoryTransactionLog')->in(Scope::SINGLETON);
}
```

## Object Life Cycle

オブジェクトライフサイクルのアノテーションを使ってオブジェクトの初期化や、PHPの終了時に呼ばれるメソッドを指定する事ができます。

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

このメソッドはPHPの **register_shutdown_function** 関数に要録されスクリプト処理が完了したとき、あるいは *exit()* がコールされたときに呼ばれます。

```php
/**
 * @PreDestroy
 */
public function onShutdown()
{
    //....
}
```
## Install

モジュールは他のモジュールの束縛をインストールして使う事ができます。

 * 同一の束縛があれば先にされた方が優先されますが
 * `$this`を渡すとそれまでの束縛をインストール先のモジュールが利用することができます。そのモジュールでの束縛は現在の束縛より優先されます。

```php
protected function configure()
{
    $this->install(new OtherModule);
    $this->install(new CustomiseModule($this);
}
```

## Automatic Injection

Ray.Diは`toInstance()`や`toProvider()`がインスタンスを渡した時に自動的にインジェクトします。
またインジェクターが作られたときにそのインジェクターはモジュールにインジェクトされます。依存にはまた違う依存があり、順に辿って依存を解決します。


## Aspect Oriented Programing

Ray.Aopのアスペクト指向プログラミングが利用できます。インターセプターの束縛はより簡単になり、アスペクトの依存解決も行われます。

```php
class TaxModule extends AbstractModule
{
    protected function configure()
    {
        $this->bindInterceptor(
            $this->matcher->subclassesOf('Ray\Di\Aop\RealBillingService'),
            $this->matcher->annotatedWith('Tax'),
            [$this->requestInjection('TaxCharger')]
        );
    }
}
```

```php
class AopMatcherModule extends AbstractModule
{
    pro
    protected function configure()
    {
        $this->bindInterceptor(
            $this->matcher->any(),                 // In any class and
            $this->matcher->startWith('delete'),   // ..the method start with "delete"
            [$this->requestInjection('Logger')]
        );
    }
}

```


Best practice
-------------

可能な限りインジェクターを直接使わないコードにします。その代わりアプリケーションのbootstrapで **ルートオブジェクト** をインジェクトするようにします。
このルートオブジェクトのクラスは依存する他のオブジェクトのインジェクションに使われます。その先のオブジェクトも同じで、依存が依存を必要として最終的にオブジェクトグラフが作られます。

Performance boost
=================

インジェクト済みオブジェクトのキャッシュを行う**CacheInjector**、あるいはオブジェクトの生成を最適化する**DiCompiler**が利用可能です。

Caching dependency-injected objects 
-----------------------------------

インジェクト済みのキャッシュを保存して利用すればパフォーマンスは大きく向上します。
**CacheInjector** はオブジェクトライフサイクルと自動生成されたアスペクトファイルのローディングも行います。
`$initialization`クロージャにはアプリケーションがコンパイル時に一度しか行わない初期化処理を記述します。

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

Dependency-injection compiler 
-----------------------------
**DiCompiler**はオブジェクト生成方法や依存関係を**インジェクションのログ**から取り出しオブジェクトの再生成（コンパイル）を最適化します。ランタイムではアノテーション利用のコストがないのはもちろん、インジェクションの設定（Module）やインジェクターも使用しません。速度やメモリ消費の点で優れます。

**制限**
 * インジェクションログからオブジェクトを生成するので全てのオブジェクトの生成をインジェクターで行う必要があります。[^1]
 * **@PreDestroy**は未サポートです。


[^1]: 例えばインターセプターを**new**で生成して`bindInterceptor()`する事はできません。`requestInject($class)`でインスタンス化したものを束縛します。**new**でオブジェクトを生成すると生成がトラックできないためです。

```php
$cache = new ApcCache;
$cacheKey = 'context-key';
$tmpDir = '/tmp';
$moduleProvider = function() {
    return new DiaryAopModule;
};
$injector = DiCompiler::create($moduleProvider, $cache, $cacheKey, $tmpDir);
$injector->getInstance('Ray\Di\DiaryInterface');

```

**Pro Tip**

deploy前に出現クラスを事前に`compile()`する事ができます。ランタイムでコンパイルコストがかかりません。
```php
$injector = DiCompiler::create($moduleProvider, $cache, $cacheKey, $tmpDir);
$injector->compile('Koriym\RayApp\Model\Author');
$injector->compile('Koriym\RayApp\Model\Diary');
...
```

Requirement
-----------

* PHP 5.4+

Documentation
-------------

Google Codeには更に詳しいGuiceのドキュメントを翻訳したものがあります。

 [http://code.google.com/p/rayphp/wiki/Motivation?tm=6](http://code.google.com/p/rayphp/wiki/Motivation?tm=6)


Installation
------------

Ray.Diをインストールにするには [Composer](http://getcomposer.org)を利用する事を勧めます。

```bash
# Install Composer
$ curl -sS https://getcomposer.org/installer | php

# Add Ray.Di as a dependency
$ php composer.phar require ray/di:*
```

Testing Ray.Di
--------------

インストールしてテストを行うにはこのようにします。

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
