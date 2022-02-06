# Ray.Di

## Dependency Injection framework #

[![Scrutinizer Code Quality](https://scrutinizer-ci.com/g/ray-di/Ray.Di/badges/quality-score.png?b=2.x)](https://scrutinizer-ci.com/g/ray-di/Ray.Di/?branch=2.x)
[![codecov](https://codecov.io/gh/ray-di/Ray.Di/branch/2.x/graph/badge.svg?token=KCQXtu01zc)](https://codecov.io/gh/ray-di/Ray.Di)
[![Type Coverage](https://shepherd.dev/github/ray-di/Ray.Di/coverage.svg)](https://shepherd.dev/github/ray-di/Ray.Di)
[![Continuous Integration](https://github.com/ray-di/Ray.Di/actions/workflows/continuous-integration.yml/badge.svg?branch=2.x)](https://github.com/ray-di/Ray.Di/actions/workflows/continuous-integration.yml)
[![Total Downloads](https://poser.pugx.org/ray/di/downloads)](https://packagist.org/packages/ray/di)

![fake](https://user-images.githubusercontent.com/529021/72650686-866ec100-39c4-11ea-8b49-2d86d991dc6d.png)

**Ray.Di**はGoogleの[Guice](http://code.google.com/p/google-guice/wiki/Motivation?tm=6)の主要な機能を持つPHPのDIフレームワークです。

## 概要

依存性注入を使用することには多くの利点がありますが、手作業でそれを行うと、しばしば大量の定型的なコードを書かなければならなくなります。Ray.Diは、依存性注入を使用するコードを、そのような定型的なコードの多くを書く手間なしに書くことができるようにするフレームワークです。

Ray.DiはあなたのPHPコードにファクトリーや`new`を使用する必要性を軽減します。ファクトリーを書く必要がある場合もありますが、あなたのコードはファクトリーに直接依存することはありません。あなたのコードは、変更、ユニットテスト、他のコンテキストでの再利用がより簡単になります。

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

- Nullオブジェクト

Ray.Di 2.0は最初に2015年にリリースされました。以来、最新のPHPに対応を続けていますが非推奨になったPHPのサポートを落とすことはあっても、後方互換性を破壊することはありません。

# 始めよう

## オブジェクトグラフの作成

DIではそのオブジェクトを作るのに必要なオブジェクトや値を**依存**としてコンストラクタで受け取ります。その依存を作るためにも、他の依存が必要です。そうやってオブジェクトを作るのには結局オブジェクトの**グラフ**が必要になります。

オブジェクトグラフを手動で作成するのは骨の折れる仕事です。テストも難しくなります。代わりにRay.Diがオブジェクトグラフを作ります。そのためには最初にどういうグラフを必要としてるのかを正確に設定しないといけません。

説明のために`BillingService`という`ProcessorInterface`と`LoggerInterface`を必要とするクラスを作ることから始めます。

```php
class BillingService
{
    public function __construct(
        private readonly ProcessorInterface $processor,
        private readonly LoggerInterface $logger
    ){}
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

コンストラクタインジェクションはインジェクションとオブジェクトの生成を一緒にします。そこで使われるコンストラクタは依存を引数として受け取ります。ほとんどの場合コンストラクタはプロパティにその依存オブジェクトを割り当てます。コンストラクタインジェクションには`#[Inject]`アトリビュートは必要ありません。

```php
public function __construct(DbInterface $db)
{
    $this->db = $db;
}
```

## セッターインジェクション

`#[Inject]`アトリビュートを与えたメソッドでメソッドインジェクションをすることができます。依存は引数の形を取り、メソッド実行前にインジェクタに解決されます。メソッドインジェクションでは任意の引数を取ることができます。メソッド名は注入には影響しません。

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

## プロパティインジェクション

プロパティインジェクションはサポートされません。

## アシスティッドインジェクション

メソッドが実行されるタイミングでメソッドの引数に依存を渡すことができます。そのためには依存を受け取る引数を`#[Assisted]`で指定してデフォルトを`null`にする必要があります。

```php
use Ray\Di\Di\Assisted;
```

```php
public function doSomething($id, #[Assisted] DbInterface $db = null)
{
    $this->db = $db;
}
```

`@Assisted`で提供される依存は、その時に渡された他の引数を参照して決定することもできます。そのためには依存を`プロバイダーバインディング`で束縛して、その[プロバイダー束縛](#プロバイダ束縛)は`MethodInvocationProvider`を依存として受け取るようにします。`get()`メソッドでメソッド実行オブジェクト [MethodInvocation](https://github.com/ray-di/Ray.Aop/blob/2.x/src/MethodInvocation.php) を取得することができ、引数の値や対象のメソッドのプロパティにアクセスすることができます。

```php
class HorizontalScaleDbProvider implements ProviderInterface
{
    public function __construct(private MethodInvocationProvider $invocationProvider)
    {}

    public function get()
    {
        $methodInvocation = $this->invocationProvider->get();
        list($id) = methodInvocation->getArguments()->getArrayCopy();
        
        return new UserDb($id); // $idによって接続データベースを切り替えます
    }
}
```

# 束縛

* [リンク束縛](#リンク束縛)
* [プロバイダ束縛](#プロバイダ束縛)
* [インスタンス束縛](#インスタンス束縛)
* [アンターゲット束縛](#アンターゲット束縛)
* [アトリビュート束縛](#アトリビュート束縛)
* [コンストラクタ束縛](#コンストラクタ束縛)
* [ヌルオブジェクト束縛](#ヌルオブジェクト束縛)


インジェクタの仕事は、オブジェクトのグラフを組み立てることです。指定された型のインスタンスを要求すると、何を構築すべきかを判断し、依存関係を解決し、すべてを一緒に配線します。依存関係を解決する方法を指定するには、バインディングを使用してインジェクタを設定します。

# 束縛の作成

束縛を作成するには、AbstractModuleを拡張しconfigureメソッドをオーバーライドします。メソッド本体でbind()をコールし各バインディングを指定します。
これらのメソッドはコンパイル時に型チェックが行われ、間違った型を使用した場合はエラーが報告されます。モジュールを作成したら、Injectorに引数として渡してインジェクタを構築します。

モジュールを使って、リンクバインディング、インスタンスバインディング、プロバイダバインディング、コンストラクタバインディング、アンターゲットバインディングを作成します。

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

## リンク束縛 ##

**Linked Bindings**は、タイプをその実装にマッピングします。この例では、インターフェース TransactionLogInterface を実装 DatabaseTransactionLog にマップしています。

```php
class BillingModule extends AbstractModule
{
    protected function configure()
    {
        $this->bind(TransactionLogInterface::class)->to(DatabaseTransactionLog::class);
    }
}
```

<a name="provider-bidning"></a>
## プロバイダ束縛

プロバイダバインディングは、タイプをそのプロバイダにマッピングします。

[Provider bindings](http://code.google.com/p/rayphp/wiki/ProviderBindings) はインターフェイスと実装クラスの**プロバイダー**を束縛します。

プロバイダクラスはRayの`ProviderInterface`を実装していて、これは値を供給するためのシンプルで一般的なインタフェースです。

```php
use Ray\Di\ProviderInterface;

interface ProviderInterface
{
    public function get();
}
```

プロバイダ実装クラスは、それ自身の依存性を持っており、それはコンストラクタを介して受け取ります。
`ProviderInterface`を実装し、完全に型安全で返されるものを定義しています。

```php
class DatabaseTransactionLogProvider implements Provider
{
    public function __construct(
        private readonly ConnectionInterface $connection
    ){}

    public function get()
    {
        $transactionLog = new DatabaseTransactionLog;
        $transactionLog->setConnection($this->connection);

        return $transactionLog;
    }
}
```
最後に、`toProvider()`メソッドを使用してプロバイダに束縛します。

```php
$this->bind(TransactionLogInterface::class)->toProvider(DatabaseTransactionLogProvider:class);
```

## インジェクションポイント

**InjectionPoint** は、インジェクションポイントの情報を持つクラスです。
メタデータへのアクセスは インジェクションされるメソッドや引数のリフレクションやアトリビュートで提供されます。

例えば、下記の`Psr3LoggerProvider`クラスの`get()`メソッドは、インジェクトする対象クラスのクラス名を利用してインジェクション可能なLoggerを生成します。

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

`InjectionPointInterface` は以下のメソッドが利用できます。

```php
$ip->getClass();      // \ReflectionClass
$ip->getMethod();     // \ReflectionMethod
$ip->getParameter();  // \ReflectionParameter
$ip->getQualifiers(); // (array) $qualifierAnnotations
```


## インスタンス束縛

`toInstance()`メソッドは値を直接束縛します。

```php
protected function configure()
{
    $this->bind(UserInterface::class)->toInstance(new User);
}
```

ある型をその型のインスタンスにバインドすることができます。これは通常、値オブジェクトのようなそれ自体に依存性を持たないオブジェクトにのみに用いるべきです。

スカラー値の定数は`#Named`を使って束縛します。

```php
protected function configure()
{
    $this->bind()->annotatedWith('login_id')->toInstance('bear');
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

## アトリビュート束縛

場合によっては、同じタイプで複数のバインディングが必要になることがあります。たとえば、PayPal のクレジットカード決済と Google Checkout の決済の両方を行いたい場合などです。
このような場合に備えて、バインディングではオプションのバインディング属性を用意しています。この属性と型を組み合わせることで、バインディングを一意に識別します。このペアをキーと呼びます。

まず、識別子(Qualifier)属性を定義します。この属性は `Qualifier` 属性のアトリビュートを付与する必要があります。

### 束縛トリビュートの定義

```php
use Ray\Di\Di\Qualifier;

#[Attribute, Qualifier]
final class PayPal
{
}
```

インジェクションされるパラメータにその属性を適用します。

```php
public function __construct(
    #[Paypal] private readonly CreditCardProcessorInterface $processor
){}
```

識別子に引数を指定することもできます。

```php
public function __construct(
    #[Paypal('processor')] private readonly CreditCardProcessorInterface $processor
){}
```

最後に、その属性を使用するバインディングを作成します。これは bind() 文のオプションである `annotatedWith` 節を使用します。

```php
protected function configure()
{
    $this->bind(CreditCardProcessorInterface::class)
        ->annotatedWith(PayPal::class)
        ->to(PayPalCreditCardProcessor::class);
```

### セッターアトリビュートの束縛

カスタムの `Qualifier`属性を、どのメソッドでもデフォルトで依存性を注入するようにするには、次のようにしますが属性を追加するには、 `RayDi⇄InjectInterface` を実装する必要があります。

```php
use Ray\Di\Di\InjectInterface;
use Ray\Di\Di\Qualifier;

#[Attribute, Qualifier]
final class PaymentProcessorInject implements InjectInterface
{
    public function isOptional()
    {
        return $this->optional;
    }
    
    public function __construct(
        public readonly bool $optional = true
        public readonly string $type;
    ){}
}
```

このインターフェースでは、`isOptional()` メソッドを実装することが必須です。
このメソッドはバインディングがオプショナルかどうかを決定します。

これでカスタムインジェクタ属性が作成できたので、任意のメソッドで使用することができます。

```php
#[PaymentProcessorInject(type: 'paypal')]
public setPaymentProcessor(CreditCardProcessorInterface $processor)
{
 ....
}
```

新しいアトリビュートを使って、インターフェイスを実装に束縛します。

```php
protected function configure()
{
    $this->bind(CreditCardProcessorInterface::class)
        ->annotatedWith(PaymentProcessorInject::class)
        ->toProvider(PaymentProcessorProvider::class);
}
```

プロバイダは、qualifier 属性で指定された情報を使用して適切なインスタンスを生成することができます。

## Qualifier

Qualifier属性の最も一般的な使用法は、関数内の引数に特定のラベルを付けることです。
このラベルは、インスタンス化されるクラスを正しく選択するためにバインディングで使用されます。このような場合Ray.Diには、文字列を受け取るビルトイン・バインディング属性`#[Named]`が用意されています。

```php
use Ray\Di\Di\Inject;
use Ray\Di\Di\Named;

public function __construct(
    #[Named('checkout')] private CreditCardProcessorInterface $processor
){}
```

特定の名前をバインドするには、`annotatedWith()` メソッドを用いてその文字列を渡します。

```php
protected function configure()
{
    $this->bind(CreditCardProcessorInterface::class)
        ->annotatedWith('checkout')
        ->to(CheckoutCreditCardProcessor::class);
}
```

パラメータを指定するには、`#[Named()]`アトリビュートを付ける必要があります。

```php
use Ray\Di\Di\Inject;
use Ray\Di\Di\Named;

public function __construct(
    #[Named('checkout')] private CreditCardProcessorInterface $processor,
    #[Named('backup')] private CreditCardProcessorInterface $subProcessor
){}
```

パラメータを指定するには、`#[Named]`アトリビュートを付ける必要があります。

```php
use Ray\Di\Di\Inject;
use Ray\Di\Di\Named;

public function __construct(
    #[Named('checkout')] private CreditCardProcessorInterface $processor,
    #[Named('backup')] private CreditCardProcessorInterface $subProcessor
){}
```

## コンストラクタ束縛

`#[Inject]`アトリビュートのないサードパーティーのクラスやアトリビュートを使いたくない時には`Provider`束縛を使うこともできますが、その場合インスタンスをユーザーコードが作成する事になりAOPが利用できません。
この問題は`toConstructor`束縛で解決できます。インターフェイスにクラスを束縛するのは`to()`と同じですが、`#[Named]`やセッターメソッドの`#[Inject]`の属性を追加する事なしに指定できます。

```php
protected function configure()
{
    $this
        ->bind($interfaceName)
        ->toConstructor(
            $className,
            $name,
            $injectionPoint,
            $postConstruct
        );
        
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

引数に識別子を追加する場合は、変数名をキー、値を識別子の名前とする配列を指定します。


```
[
	[$param_name1 => $binding_name1],
	...
]
```

以下のストリングフォーマットもサポートされています。
`'param_name1=binding_name1&...'`

**setter_injection**

`InjectionPoints`オブジェクトでセッターインジェクトのメソッド名($methodName)とQualifier($named)を指定します。

```php
(new InjectionPoints)
	->addMethod($methodName1)
	->addMethod($methodName2, $named)
  ->addOptionalMethod($methodName, $named)
```

**postCosntruct**

Ray.Diは、そのコンストラクタとセッタメソッドを呼び出してバインディングを満たし、すべての依存関係が注入された後に`$postCosntruct`メソッドを呼び出します。

## PDO Example

[PDO](http://php.net/manual/ja/pdo.construct.php)クラスの束縛の例です. 

```php
public PDO::__construct (string $dsn [, string $username [, string $password [, array $options ]]] )
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

## ヌルオブジェクト束縛

Null Objectはインターフェイスを実装していてもメソッドの中で何もしないオブジェクトです。
`toNull()`で束縛するとインターフェイスからNullオブジェクトのコード生成され、そのインスタンスにバインドされます。

```php
protected function configure()
{
    $this->bind(CreditCardProcessorInterface::class)->toNull();
}
```

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

``php
use Ray\Di\Di\PostConstruct;
```
```php
#[PostConstruct]
public function onInit()
{
    //....
}
```

# アスペクト指向プログラミング

依存性注入を補完するために、Ray.Diはメソッドインターセプションをサポートしています。この機能により、一致するメソッドが呼び出されるたびに実行されるコードを書くことができます。これは、トランザクション、セキュリティ、ロギングなど、横断的な関心事（アスペクト）に適しています。インターセプターは問題をオブジェクトではなくアスペクトに分割するため、その利用はアスペクト指向プログラミング（AOP）と呼ばれています。

選択したメソッドを平日のみとするために、アトリビュートを定義します。

```php
#[Attribute(Attribute::TARGET_METHOD)]
final class NotOnWeekends
{
}
```

...そして、必要なメソッドに適用します。

```php
class BillingService
{
    #[NotOnWeekends]
    chargeOrder(PizzaOrder $order, CreditCard $creditCard)
    {
```

次に `MethodInterceptor`インターフェイスを実装し、インターセプターを定義します。メソッドを呼び出す必要がある場合は、`$invocation->proceed()` を呼び出すことで呼び出します。

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

最後に、すべての設定を行います。この場合、どのクラスにもマッチしますが、`#[NotOnWeekends]` 属性を持つメソッドにのみマッチします。

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

それをすべてまとめると、（土曜日まで待つとして）メソッドがインターセプトされ、注文が拒否されたことがわかります。

```php
RuntimeException: chargeOrder not allowed on weekends! in /apps/pizza/WeekendBlocker.php on line 14

Call Stack:
    0.0022     228296   1. {main}() /apps/pizza/main.php:0
    0.0054     317424   2. Ray\Aop\Weaver->chargeOrder() /apps/pizza/main.php:14
    0.0054     317608   3. Ray\Aop\Weaver->__call() /libs/Ray.Aop/src/Weaver.php:14
    0.0055     318384   4. Ray\Aop\ReflectiveMethodInvocation->proceed() /libs/Ray.Aop/src/Weaver.php:68
    0.0056     318784   5. Ray\Aop\Sample\WeekendBlocker->invoke() /libs/Ray.Aop/src/ReflectiveMethodInvocation.php:65
```

インターセプターを無効にするには、NullInterceptorをバインドします。

```php
use Ray\Aop\NullInterceptor;

protected function configure()
{
    // ...
    $this->bind(LoggerInterface::class)->to(NullInterceptor::class);
}
```


# More Docs

* [Contextual binding](docs/contextual_binding.md)
* [Performance Boost](docs/performance_boost.md)

## Ray.Diアプリケーションをグラフに

高度なアプリケーションを作成した場合、Ray.Diの豊富なイントロスペクションAPIでオブジェクトグラフを詳細に記述することができます。オブジェクトビジュアルグラファーは、このデータを理解しやすいビジュアライゼーションとして公開します。複雑なアプリケーションの複数のクラスのバインディングや依存関係を、統一されたダイアグラムで表示することができます。

詳細は [Ray.ObjectGrapher](https://github.com/koriym/Ray.ObjectGrapher)をご覧ください。

## アノテーション / アトリビュート

Ray.Di は、PHP 7/8 では [doctrine/annotation](https://github.com/doctrine/annotations)を使用して、PHP8では[Attributes](https://www.php.net/manual/en/language.attributes.overview.php)を使用して使用することができます。アノテーションコードの例は古い[README(v2.10)](https://github.com/ray-di/Ray.Di/tree/2.10.5/README.md)をご覧ください。属性に対する前方互換性のあるアノテーションを作成するには、 [カスタムアノテーションクラス](https://github.com/kerveros12v/sacinta4/blob/e976c143b3b7d42497334e76c00fdf38717af98e/vendor/doctrine/annotations/docs/en/custom.rst#optional-constructors-with-named-parameters) を参照してください。

## モジュールインストール

モジュールは他のモジュールの束縛をインストールして使う事ができます。

 * 同一の束縛があれば先にされた方が優先されますが`overrinde`でインストールすると後からのモジュールが優先されインストールされます。

```php
protected function configure()
{
    $this->install(new OtherModule);
    $this->override(new CustomiseModule);
}
```

# インストール

Ray.Diをインストールにするには [Composer](http://getcomposer.org)を利用します。

```bash
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

## フレームワーク統合

 * [BEAR.Sunday](https://github.com/koriym/BEAR.Sunday)
 * [CakePHP3 PipingBag](https://github.com/lorenzo/piping-bag) by [@jose_zap](https://twitter.com/jose_zap)
 * [Yii 1](https://github.com/koriym/Ray.Dyii)

*このドキュメントの大部分は、[Google Guice](https://github.com/google/guice/)のサイトから翻訳引用しています。*
