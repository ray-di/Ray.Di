# Ray.Di

## Dependency Injection framework #

[![Scrutinizer Code Quality](https://scrutinizer-ci.com/g/ray-di/Ray.Di/badges/quality-score.png?b=2.x)](https://scrutinizer-ci.com/g/ray-di/Ray.Di/?branch=2.x)
[![Code Coverage](https://scrutinizer-ci.com/g/ray-di/Ray.Di/badges/coverage.png?b=2.x)](https://scrutinizer-ci.com/g/ray-di/Ray.Di/?branch=2.x)
[![Build Status](https://scrutinizer-ci.com/g/ray-di/Ray.Di/badges/build.png?b=2.x)](https://scrutinizer-ci.com/g/ray-di/Ray.Di/build-status/2.x)
[![Build Status](https://travis-ci.org/ray-di/Ray.Di.svg?branch=2.x)](https://travis-ci.org/ray-di/Ray.Di)
[![Total Downloads](https://poser.pugx.org/ray/di/downloads)](https://packagist.org/packages/ray/di)

**Ray.Di**はGoogleの[Guice](http://code.google.com/p/google-guice/wiki/Motivation?tm=6)の主要な機能を持つPHPのDIフレームワークです。

## 概要

Ray.Diには以下の機能があります。

- コンストラクタインジェクションとセッターインジェクション

- 自動ワイアリング

- コンストラクタの後の初期化

- PHPのファクトリーコード生成

- 名前付きインターフェイス

- インジェクションポイントメタデータ

- インスタンスファクトリー

- アノテーションはオプション

- AOP統合

# 始めよう

## オブジェクトグラフの作成

DIではそのオブジェクトを作るのに必要なオブジェクトや値を**依存**としてコンストラクタで受け取ります。その依存を作るためにも、他の依存が必要です。そうやってオブジェクトを作るのには結局オブジェクトの**グラフ**が必要になります。

オブジェクトグラフを手動で作成するのは骨の折れる仕事です。テストも難しくなります。代わりにRay.Diがオブジェクトグラフを作ります。そのためには最初にどういうグラフを必要としてるのかを正確に設定しないといけません。

説明のために`BillingService`という`ProcessorInterface`と`LoggerInterface`を必要とするクラスを作ることから始めます。

```php
class BillingService
{
    private $processor;
    private $logger
    
    public function __construct(ProcessorInterface $processor, LoggerInterface $logger)
    {
        $this->processor = $processor;
        $this->logger = $logger;
    }
}
```

Ray.Di はインターフェイスとその実装をマップするために束縛(バインド）します。`モジュール`はその束縛を英語のように表記したものの集まりです。

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

`Ray.Di` がオブジェクトグラフを作るためにはモジュールが必要です。まずはモジュールでインジェクターを作って、そのインジェクターで`BillingService`を組み立てます。

```php
$injector = new Injector(new BillingModule);
$billingService = $injector->getInstance(BillingService::class);
```

# インジェクション

## コンストラクタインジェクション

コンストラクタインジェクションはインジェクションとオブジェクトの生成を一緒にします。そこで使われるコンストラクタは依存を引数として受け取ります。ほとんどの場合コンストラクタはプロパティにその依存オブジェクトを割り当てます。コンストラクタインジェクションには`@Inject`アノテーションは必要ありません。

```php
    public function __construct(DbInterface $db)
    {
        $this->db = $db;
    }
```

## セッターインジェクション

`@Inject` アノテーションを持つメソッドでメソッドインジェクションをすることができます。依存は引数の形を取り、メソッド実行前にインジェクタに解決されます。メソッドインジェクションでは任意の引数を取ることができます。メソッド名は注入には影響しません。

```php
    /**
     * @Inject
     */
    public function setDb(DbInterface $db)
    {
        $this->db = $db;
    }
```

## プロパティインジェクション

プロパティインジェクションはサポートされません。

## アシスティッドインジェクション

メソッドが実行されるタイミングでメソッドの引数に依存を渡すことができます。そのためには依存を受け取る引数を`@Assisted`で指定し、引数リストの終わり（右）に移動して`null`をデフォルトとして与える必要があります。

```php
use Ray\Di\Di\Assisted;
```

```php
    /**
     * @Assisted({"db"})
     */
    public function doSomething($id, DbInterface $db = null)
    {
        $this->db = $db;
    }
```

`@Assisted`で提供される依存は、その時に渡された他の引数を参照して決定することもできます。そのためには依存を`プロバイダーバインディング`で束縛して、その[プロバイダー束縛](#provider-bidning)は`MethodInvocationProvider`を依存として受け取るようにします。`get()`メソッドでメソッド実行オブジェクト [MethodInvocation](https://github.com/ray-di/Ray.Aop/blob/2.x/src/MethodInvocation.php) を取得することができ、引数の値や対象のメソッドのプロパティにアクセスすることができます。

```php
class HorizontalScaleDbProvider implements ProviderInterface
{
    /**
     * @var MethodInvocationProvider
     */
    private $invocationProvider;

    public function __construct(MethodInvocationProvider $invocationProvider)
    {
        $this->invocationProvider = $invocationProvider;
    }

    public function get()
    {
        $methodInvocation = $this->invocationProvider->get();
        list($id) = methodInvocation->getArguments()->getArrayCopy();
        
        return new UserDb($id); // $idによって接続データベースを切り替えます
    }
}
```

# 束縛

インジェクタの役目はオブジェクトグラフの構築です。インスタンスを型で要求されると生成すべきものを見つけて依存解決し、それらを結びつけます。依存解決の方法を指定するにはインジェクタに束縛で設定を行います。

## リンク束縛 ##

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

<a name="provider-bidning"></a>
## プロバイダ束縛

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
## コンテンキストプロバイダ束縛

同じプロバイダーでコンテキスト別にオブジェクトを生成したい場合があります。例えば接続先の違う複数のDBオブジェクトを同じインターフェイスでインジェクトしたい場合などです。そういう場合には`toProvider()`でコンテキスト（文字列）を指定して束縛をします。

```php
$dbConfig = ['user' => $userDsn, 'job'=> $jobDsn, 'log' => $logDsn];
$this->bind()->annotatedWith('db_config')->toInstance(dbConfig);
$this->bind(Connection::class)->annotatedWith('usr_db')->toProvider(DbalProvider::class, 'user');
$this->bind(Connection::class)->annotatedWith('job_db')->toProvider(DbalProvider::class, 'job');
$this->bind(Connection::class)->annotatedWith('log_db')->toProvider(DbalProvider::class, 'log');
```

プロバイダーはコンテキスト別に生成します。

```php
class DbalProvider implements ProviderInterface, SetContextInterface
{
    private $dbConfigs;

    public function setContext($context)
    {
        $this->context = $context;
    }

    /**
     * @Named("db_config")
     */
    public function __construct(array $dbConfigs)
    {
        $this->dbConfigs = $dbConfigs;
    }

    /**
     * {@inheritdoc}
     */
    public function get()
    {
        $config = $this->dbConfigs[$this->context];
        $conn = DriverManager::getConnection(config);

        return $conn;
    }
}
```
同じインターフェイスですが、接続先の違う別々のDBオブジェクトを受け取ります。

```php
/**
 * @Named("userDb=user_db,jobDb=job_db,logDb=log_db")
 */
public function __construct(Connection $userDb, Connection $jobDb, Connection $logDb)
{
  //...
}
```


## 名前束縛

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

## インスタンス束縛

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

## アンターゲット束縛

ターゲットを指定しないで束縛をつくることができ、コンクリートクラスの束縛に便利です。事前にインジェクターに型の情報を伝えるので束縛を事前に行いエラー検知や最適化を行うことができます。
Untargetted bindingsは以下のように`to()`が必要ありません。

```php

protected function configure()
{
    $this->bind(MyConcreteClass::class);
    $this->bind(AnotherConcreteClass::class)->in(Scope::SINGLETON);
}
```

## コンストラクタ束縛

`@Inject`アノテーションのないサードパーティーのクラスやアノテーションを使いたくない時には`Provider`束縛を使うこともできますが、その場合インスタンスをユーザーコードが作成する事になりAOPが利用できません。

この問題は`toConstructor`束縛で解決できます。インターフェイスにクラスを束縛するのは`to()`と同じですが、`@Named`やセッターメソッドの`@Inject`の指定をアノテートする事なしに指定できます。

```php
<?php
class WebApi implements WebApiInterface
{
    private $id;
    private $password;
    private $client;
    private $token;

    /**
     * @Named("id=user_id,password=user_password")
     */
    public function __construct(string $id, string $password)
    {
        $this->id = $id;
        $this->password = $password;
    }
    
    /**
     * @Inject
     */
    public function setGuzzle(ClientInterface $client)
    {
        $this->client = $client;
    }

    /**
     * @Inect(optional=true)
     * @Named("token")
     */
    public function setOptionalToken(string $token)
    {
        $this->token = $token;
    }

    /**
     * @PostConstruct
     */
    public function initialize()
    {
    }
```

上記のWebApiクラスをアノテーションを使わないでコンストラクタ束縛するにはこのようにします。
    

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

クラス名

**name**

名前束縛。配列か文字列で`引数名`と`束縛名の名前`をペアにして指定します。

array `[[$parame_name => $binding_name],...]` or string `"param_name=binding_name&..."`

**setter_injection**

セッターインジェクションのメソッド名と`束縛名の名前`を指定したインジェクとポイントオブジェクト

**postCosntruct**
 
`＠postCosntruct`と同じく全てのインジェクションが終わった後に呼ばれる初期化メソッド名。

## PDO Example

[PDO](http://php.net/manual/ja/pdo.construct.php)クラスの束縛の例です. 

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
    $this->bind()->annotatedWith('pdo_username')->toInstance($username);
    $this->bind()->annotatedWith('pdo_password')->toInstance($password);
}
```

PDOのどのインターフェイスがないので`toConstructor()`メソッドの二番目の引数の名前束縛でP束縛しています

## スコープ

デフォルトでは、Rayは毎回新しいインスタンスを生成しますが、これはスコープの設定で変更することができます。

```php
protected function configure()
{
    $this->bind(TransactionLog::class)->to(InMemoryTransactionLog::class)->in(Scope::SINGLETON);
}
```

## オブジェクトライフサイクル

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
## インストール

モジュールは他のモジュールの束縛をインストールして使う事ができます。

 * 同一の束縛があれば先にされた方が優先されますが`overrinde`でインストールすると後からのモジュールが優先されインストールされます。

```php
protected function configure()
{
    $this->install(new OtherModule);
    $this->override(new CustomiseModule);
}
```

## アスペクト指向プログラミング

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

# ベストプラクティス

可能な限りインジェクターを直接使わないコードにします。その代わりアプリケーションのbootstrapで **ルートオブジェクト** をインジェクトするようにします。
このルートオブジェクトのクラスは依存する他のオブジェクトのインジェクションに使われます。その先のオブジェクトも同じで、依存が依存を必要として最終的にオブジェクトグラフが作られます。

## パフォーマンスの向上

### スクリプトインジェクタ

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
    $instance = $injector->getInstance(ListerInterface::class);
}
```

インスタンスが生成されれば、生成されたファクトリーファイルを `$tmpDir` で見ることができるようになります。

### キャッシュインジェクタ

シリアライズをして高速にインジェクションを行うようにすることもできます。

```php

// save
$injector = new Injector(new ListerModule);
$cachedInjector = serialize($injector);

// load
$injector = unserialize($cachedInjector);
$lister = $injector->getInstance(ListerInterface::class);
```

## Grapher

`Grapher`では、コンストラクタの引数は手動で渡しその後のインジェクションを自動で行われます。
（ルートオブジェクトのみオブジェクトの生成の仕組みをもつような）既存のシステムにRay.Diを導入するために便利です。AOPなども同様に使えます。

```php
// ...
$grapher = new Grapher(new Module, __DIR__ . '/tmp');
$instance = $grapher->newInstanceArgs(FooController::class, [$param1, $param2]);
```

Ray.Compilerは使用できません。パフォーマンス向上にはインジェクタをシリアライズして使います。

## フレームワーク

 * [CakePHP3 PipingBag](https://github.com/lorenzo/piping-bag) by [@jose_zap](https://twitter.com/jose_zap)
 * [Symfony QckRayDiBundle](https://github.com/qckanemoto/QckRayDiBundle) and [sample project](https://github.com/qckanemoto/symfony-raydi-sample) by [@qckanemoto](https://twitter.com/qckanemoto)
 * [Radar](https://github.com/ray-di/Ray.Adr)
 * [BEAR.Sunday](https://github.com/koriym/BEAR.Sunday)
 * [Yii 1](https://github.com/koriym/Ray.Dyii)
 
 
## モジュール

`Ray.Di` のためのさまざまなモジュールが利用可能です。 https://github.com/Ray-Di

## インストール

Ray.Diをインストールにするには [Composer](http://getcomposer.org)を利用します。

```bash
# Add Ray.Di as a dependency
$ composer require ray/di ^2.0
```

## テスト

インストールしてテストとデモプログラムを実行します。

```bash
$ git clone https://github.com/ray-di/Ray.Di.git
$ cd Ray.Di
$ ./vendor/bin/phpunit
$ php demo/run.php
```
