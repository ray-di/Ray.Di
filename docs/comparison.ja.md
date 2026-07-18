# Ray.Di と他の PHP DI コンテナの技術比較

対象読者は DI の基本概念(コンストラクタ注入・束縛・スコープ)を理解している方です。DI そのものの説明はしません。また、このドキュメントが扱うのは設計と実装の技術的な違いだけです。人気やエコシステムの規模は扱いません。

深く比較する対象は PHP-DI 7、Symfony DependencyInjection(7.x)、Laravel サービスコンテナ(12.x)の 3 つです。Laminas ServiceManager、Aura.Di、Pimple、League/Container には必要な箇所で触れます。他コンテナに関する記述は 2026 年 7 月時点の各公式ドキュメントに基づきます。

Ray.Di は [Google Guice](https://github.com/google/guice/wiki) の設計を PHP に移した DI/AOP フレームワークです。以下で説明する違いの多くは Guice の設計判断の継承で、本文中で対応関係を示します。

## 要約

| # | 観点 | Ray.Di | 比較対象コンテナの代表的な設計 |
|---|------|--------|--------------------------------|
| 1 | 束縛のキー | 型 + 修飾子 | 文字列サービス ID(PSR-11 `get($id)`) |
| 2 | 設定の表現 | 型付き PHP コード(Fluent DSL + 属性)。束縛時に型を検証 | YAML / XML / PHP 配列 / クロージャ登録(+ 属性) |
| 3 | 設定の合成 | モジュール(`install` / `override`)。衝突時の優先順位は契約テストで仕様化 | 定義ファイルの読み込み順・Provider 登録順(後勝ち) |
| 4 | AOP | matcher で選んだメソッド群への interceptor 織り込みを束縛と同じモジュールで宣言 | なし(デコレーションなどで代替) |
| 5 | スコープの既定 | Prototype(毎回生成) | shared / singleton(PHP-DI、Symfony)。Laravel の `bind()` は毎回生成 |
| 6 | 実行戦略 | ランタイム + 直列化キャッシュ + 任意コンパイル(ray/compiler) | Laravel: ランタイム / PHP-DI: + 任意コンパイル / Symfony: コンパイル前提 |
| 7 | 注入先の文脈 | `InjectionPoint`・context 付き Provider・`toConstructor`・`Map` | contextual binding(Laravel)や `#[Target]`(Symfony)が部分的に対応 |
| 8 | 診断 | 依存チェーン付き `Unbound`・`toNull()`・束縛スナップショット | `debug:container` / `lint:container`(Symfony)など |

番号は以下の節に対応します。

## 1. 束縛のキー — 型と修飾子

Ray.Di のエントリポイントは次の 1 メソッドです。

```php
public function getInstance($interface, $name = Name::ANY);
```

キーは「型 + 修飾子」の複合キーです。同じ `FinderInterface` でも `#[Legacy]` 付きの要求と無印の要求は別の束縛に解決されます。ジェネリクスアノテーションにより、静的解析上 `getInstance(Foo::class)` の戻り値は `mixed` ではなく `Foo` です(`src/di/InjectorInterface.php`)。

比較対象のエントリポイントは PSR-11(`get(string $id): mixed`)です。キーは文字列 ID、戻り値は `mixed` で、PHP-DI・Symfony・Laravel はいずれもこれを実装しています。クラス名を ID に使う慣習はあっても契約上は文字列で、「型 + 修飾子」の組をキーの型として表現することはできません。

置き場所の想定も違います。`InjectorInterface` の phpdoc は「この舞台裏の動作こそが DI をその親戚であるサービスロケータパターンと区別するものだ」と述べています。`getInstance()` は composition root でルートオブジェクトを 1 回取得するための API で、オブジェクトグラフの内側にコンテナ(injector)が現れないことが設計目標です。

なお「束縛していない具象クラスも解決できる」点(untargeted / JIT 束縛)は PHP-DI や Laravel の autowiring と同等の機能で、差別化点ではありません。差が出るのは失敗系です。修飾子付きの要求は JIT 束縛にフォールバックせず、即座に `Unbound` になります(`src/di/Injector.php`)。

## 2. 設定の表現 — 束縛は型検査される PHP の式

「同じインターフェースに 2 つの実装があり、特定の消費者には旧実装を注入したい」という定番の課題で比べます。

Ray.Di では、注入側は修飾子属性で要求を宣言します。修飾子は `#[Qualifier]` メタ属性で自作する、名前空間を持つ普通のクラスです(`demo/02a-named-by-qualifier.php`)。

```php
#[Attribute(Attribute::TARGET_PARAMETER), Qualifier]
class Legacy
{
}

class MovieLister
{
    public function __construct(
        #[Legacy] public readonly FinderInterface $finder
    ) {}
}
```

束縛はモジュールの `configure()` に書く PHP の式です。

```php
class FinderModule extends AbstractModule
{
    protected function configure(): void
    {
        $this->bind(FinderInterface::class)->annotatedWith(Legacy::class)->to(LegacyFinder::class);
        $this->bind(MovieListerInterface::class)->to(MovieLister::class);
    }
}
```

同じことを比較対象で書くと次のようになります。

```php
// PHP-DI 7(定義ファイル)
return [
    'finder.legacy' => DI\autowire(LegacyFinder::class),
    MovieListerInterface::class => DI\autowire(MovieLister::class)
        ->constructorParameter('finder', DI\get('finder.legacy')),
];
```

```yaml
# Symfony(services.yaml)
services:
    app.finder.legacy:
        class: App\LegacyFinder
    App\MovieLister:
        arguments:
            $finder: '@app.finder.legacy'
```

```php
// Laravel(ServiceProvider::register())
$this->app->when(MovieLister::class)
          ->needs(FinderInterface::class)
          ->give(LegacyFinder::class);
```

どれでも課題は解けます。違いは束縛の知識がどこに、どんな形式で存在するかです。

- **識別子の性質。** `'finder.legacy'` や `'@app.finder.legacy'` は構成データ上の文字列で、その同一性を検証するのはコンテナだけです。Ray.Di の `Legacy::class` はオートローダと静的解析の管理下にあるクラス名で、IDE の参照検索・一括リネーム・デッドコード検出が束縛にも効きます。
- **検証のタイミング。** Ray.Di は束縛を書いた時点で検証します。`to()` は実装クラスがインターフェースを実装しているかを検査し、違反すると `configure()` の実行時に `InvalidType` を投げます。存在しないクラス名は `NotFound` になります(`src/di/BindValidator.php`)。文字列 ID の綴り間違いが「別の ID の未定義」として解決時まで生き残る、ということが起きません。
- **設定と型システムの距離。** psalm / PHPStan は `configure()` の中身を普通の PHP として検査します。YAML / XML はもちろん、`DI\get('finder.legacy')` の文字列も型システムの外です。

公平のために書くと、「設定を PHP で書けるか」自体はもう差ではありません。Symfony は PHP 設定と `#[Autowire]` / `#[Target]` 属性を持ち、PHP-DI も `useAttributes(true)` で `#[Inject]` 属性が使えます。Aura.Di は Ray.Di に最も近い「PHP コードで constructor パラメータを構成する」コンテナです。差が残るのは、束縛の宣言そのものが型検査対象の第一級の式か、それとも文字列キーを介した構成データか、という点です。

## 3. 設定の合成 — モジュールと、仕様化された優先順位

Ray.Di の設定の単位は束縛の集合を持つモジュールで、モジュール同士は演算で合成します。

```php
class ProdModule extends AbstractModule
{
    protected function configure(): void
    {
        $this->install(new DbModule());        // 取り込み: 既にある束縛が優先
        $this->override(new CdnModule());      // 上書き: 引数側の束縛が優先
    }
}

$injector = new Injector(new ProdModule(new AppModule())); // チェーン: 外側が優先
```

Guice の `install()` / `Modules.override()` に対応する語彙です。このほかに、ラップしたモジュールの束縛を別の名前に移してデコレータモジュールを作る `rename()` があります。

重要なのは演算の存在よりも、**同じキーに複数の束縛が到達したときにどれが勝つかが仕様として明文化され、契約テストで固定されている**ことです。`tests/di/README.md` から転載します。

| 経路 A | 経路 B | 勝者 |
|--------|--------|------|
| 自身の `bind()` | コンストラクタチェーンのモジュール | 自身の `bind()` |
| 自身の `install()` | コンストラクタチェーンのモジュール | 自身の `install()` |
| `bind()` | `install()`(順序不問) | `bind()` |
| 最初の `install()` | 2 番目の `install()` | 最初 |
| チェーン外側のモジュール | チェーン内側のモジュール | 外側 |
| `override()` の対象 | 既に束縛済みの何であれ | `override()` の対象 |

原理は 3 つです。自分のモジュールに直接書いた `bind()` が最優先。`install()` とチェーンの合成は既存優先のマージ(先勝ち)。上書きは `override()` という明示的な操作。つまり **後から取り込んだものが黙って勝つことがありません**。モジュールリスト(`new Injector([$m1, $m2])`)も同じ原理で最初のモジュールが勝ちます。AOP の pointcut とマルチバインディング(4 節・7 節)は宣言順に追記され、先に宣言した interceptor が最外に、先に宣言したエントリが Map の先頭になります。

比較対象では、同じサービス ID への再定義は「後勝ち」が原則です。Symfony は設定ファイルの読み込み順で後の定義が勝ち、PHP-DI は `addDefinitions()` の順で後の定義が勝ち(ドキュメント上も明記された上書き手段です)、Laravel は同じ abstract への `bind()` が前の束縛を置き換えます。一方で「複数の到達経路が衝突したときの優先順位」を仕様文書と契約テストの両方で固定した対応物は、比較対象 3 コンテナの公式ドキュメントには見当たりませんでした。

Ray.Di がここまでやる動機は事故の経験です。PR #319 は 100% のブランチカバレッジを保ったままモジュール合成の優先順位を反転させました。実行されるコードは同じで、勝者だけが変わったからです。以来、合成の意味論は `ModuleCompositionTest` が「どの具象クラスに解決されたか」を assert する形で仕様化されています。合成した設定の意味論に依存するアプリケーション(モジュールを配布・再利用する場合は特に)にとって、これは「実装の癖」と「仕様」の違いです。

## 4. AOP — 束縛と同じ言語で宣言する横断的関心事

Ray.Di を他の DI コンテナと並べたとき、カテゴリごと存在しないのが AOP です。メソッドインターセプションを DI の一部として持つ設計は Guice 直系で、比較対象 3 コンテナはいずれもこの機能を中核に持ちません。

interceptor は `MethodInterceptor` を実装します。

```php
class TimerInterceptor implements MethodInterceptor
{
    public function invoke(MethodInvocation $invocation)
    {
        $start = microtime(true);
        $result = $invocation->proceed();  // 元のメソッドを実行
        printf("%s: %.3f sec\n", $invocation->getMethod()->getName(), microtime(true) - $start);

        return $result;
    }
}
```

どこに織り込むかは、束縛と同じ `configure()` の中で matcher の述語で宣言します。

```php
class TimerModule extends AbstractModule
{
    protected function configure(): void
    {
        $this->bindInterceptor(
            $this->matcher->any(),                        // どのクラスでも
            $this->matcher->annotatedWith(Timed::class),  // #[Timed] の付いたメソッドに
            [TimerInterceptor::class]
        );
    }
}
```

実装は Ray.Aop で、対象クラスを継承したプロキシクラスを生成して tmp ディレクトリにキャッシュし、Injector が自動ロードします。interceptor 自身も DI の対象です(`bindInterceptor()` が singleton として自動束縛するため、interceptor はコンストラクタで依存を注入できます)。pointcut の合成は 3 節の優先順位仕様に含まれます。

比較対象の代替手段は「デコレーション」です。Symfony はサービスデコレーション(`decorates:` / `#[AsDecorator]`)、PHP-DI は `DI\decorate()`、Laravel は `extend()` を持ちます。技術的な違いは選択の単位です。デコレーションは特定のサービス 1 つを手書きのラッパークラスで包みます。AOP は述語(matcher)で横断的に選んだメソッド群を、対象を知らない interceptor のチェーンで包みます。「`#[Timed]` が付いた全メソッド」「名前が `save` で始まる全メソッド」という宣言は、デコレーションでは対象の数だけラッパーを書くことになります。

代償も書いておきます。織り込みはクラス継承ベースのため `final` クラス・`final` メソッドには適用できません。また実行スタックにプロキシが挟まるため、デバッグ時のトレースは 1 段深くなります。横断的関心事が少ないアプリケーションでは、デコレータを数枚手書きするほうが仕掛けとして小さいのも事実です。

## 5. スコープ — 既定は Prototype

Ray.Di の束縛は既定で Prototype、つまり解決のたびに新しいインスタンスを作ります。共有したいときだけ明示します。

```php
$this->bind(FinderInterface::class)->to(Finder::class)->in(Scope::SINGLETON);
```

これは Guice の「unscoped が既定」の移植です。Injector はインスタンスを作って注入したら忘れる、覚えておくこと(共有)は束縛に書かれた設計判断、という立場です。

比較対象の既定は分かれています。PHP-DI は 6 以降スコープの概念自体を廃止し、すべての定義がコンテナの生存期間で 1 インスタンスです(毎回生成したい場合は `make()` かファクトリを使います)。Symfony のサービスは既定で shared で、`shared: false` を指定したものだけ毎回生成されます。Laravel は `bind()` が毎回生成、`singleton()` / `scoped()` が明示的な共有で、既定に関しては Ray.Di に近い側です。

この違いが表面化するのは長寿命プロセスです。リクエストごとにプロセスが死ぬ従来型 PHP では「コンテナの生存期間 = リクエスト」なので shared 既定は実質無害でした。Laravel Octane やキューワーカーのようにコンテナがリクエストをまたいで生きる環境では、shared 既定は状態の持ち越しとして現れます(Laravel の `scoped()` はまさにこのための中間スコープです)。Prototype 既定は逆方向の割り切りで、「共有されるオブジェクトの一覧」が `in(Scope::SINGLETON)` の grep で列挙できることを意味します。

## 6. 実行戦略 — ランタイム、直列化、コンパイルの 3 段

いつリフレクションを払い、いつコードを生成するかはコンテナごとに戦略が分かれます。

- **Laravel** は純粋なランタイム解決です。解決のたびにリフレクションを使います(結果の一部はキャッシュされますが、コンテナのコード生成はありません)。
- **PHP-DI** は既定でランタイムリフレクション、`enableCompilation()` で定義をコンパイルできます。
- **Symfony** はコンパイル前提です。コンテナはビルド工程で 1 つの PHP クラスに dump され、実行時はそれが動きます(開発時は自動再コンパイル)。
- **Ray.Di** は既定でランタイムリフレクションです。コード生成されるのは AOP プロキシだけで、これは tmp ディレクトリにキャッシュされます。

Ray.Di が特徴的なのはその上の 2 段です。第一に、Injector はオブジェクトグラフの解析結果ごと直列化できます(`demo/10-cache.php`)。

```php
file_put_contents($file, serialize(new Injector(new FinderModule(), $tmpDir)));
// 次のリクエストから
$injector = unserialize(file_get_contents($file));
```

`__wakeup()` が生成済みクラスのオートローダを再登録するため、アノテーション読み取りやリフレクションのコストは初回だけになります。

第二に、別パッケージ ray/compiler(composer.json の suggest)を使うと、全束縛を PHP スクリプトにコンパイルして DI をランタイムから消せます(`demo/11-script-injector.php`)。

```php
(new DiCompiler(new FinderModule(), $tmpDir))->compile();
$injector = new ScriptInjector($tmpDir);  // 生成済みスクリプトを実行するだけ
```

つまり Symfony が「常にコンパイル」、Laravel が「常にランタイム」という単一戦略なのに対し、Ray.Di と PHP-DI は「開発はランタイム、本番はキャッシュ/コンパイル」を選べる二段構えです。Symfony のコンパイル前提には、ビルド時点で設定の誤りを検出できるという Ray.Di にない利点があり、これは 9 節で扱います。

## 7. 注入の語彙 — 「何に注入されるか」に依存する束縛

注入される値が注入先に依存するケース(注入先クラス名を知るロガー、消費者ごとに違う設定値)は、文字列 ID → 値のレジストリでは表現しにくい領域です。Ray.Di はここに複数の語彙を持っています。4 つだけコード付きで示します。

**InjectionPoint。** Provider は自分が今どこに注入しようとしているかのメタデータ(注入先のクラス・メソッド・パラメータとその属性)を参照できます(`demo/03-injection-point.php`)。

```php
class FinderProvider implements ProviderInterface
{
    public function __construct(
        private readonly InjectionPointInterface $ip
    ) {}

    public function get(): Finder
    {
        return new Finder($this->ip->getClass()->getName());  // 注入先クラス名を渡す
    }
}
```

定番はチャンネル名を注入先クラスにしたロガーの注入です。Laravel の contextual binding は「注入先クラス単位の切り替え」を宣言的に行うもので、これの部分集合に相当します。注入先のリフレクションメタデータへの汎用アクセスに相当する API は、比較対象では見当たりませんでした。

**context 付き Provider。** 同じ Provider クラスをコンテキスト文字列だけ変えて複数の束縛に使えます。

```php
$this->bind(DbInterface::class)->annotatedWith('users')
    ->toProvider(DbProvider::class, 'user_db');
$this->bind(DbInterface::class)->annotatedWith('jobs')
    ->toProvider(DbProvider::class, 'job_db');
```

Provider は `SetContextInterface` を実装して文字列を受け取ります。接続先だけ違う DB 接続のような「ほぼ同じ生成ロジック」を Provider の複製なしに束縛できます。

**toConstructor。** 属性を付けられないサードパーティクラスは、コンストラクタ変数名と束縛名の対応を外から与えます(`demo/05a-constructor-binding.php`)。

```php
$this->bind(PDO::class)->toConstructor(PDO::class, ['dsn' => 'pdo_dsn']);
$this->bind()->annotatedWith('pdo_dsn')->toInstance('sqlite::memory:');
```

2 行目は型のない名前だけの束縛で、スカラー値も同じ仕組みに乗ります。setter 指定(`InjectionPoints`)と `postConstruct` も同時に指定できます。

**マルチバインディング。** 同じインターフェースの実装を複数のモジュールから寄せ集め、遅延評価の `Map` として注入します。

```php
// 複数のモジュールに分散していてよい
MultiBinder::newInstance($this, EngineInterface::class)
    ->addBinding('gas')->to(GasEngine::class);
MultiBinder::newInstance($this, EngineInterface::class)
    ->addBinding('electric')->to(ElectricEngine::class);
```

```php
class Car
{
    /** @param Map<EngineInterface> $engines */
    public function __construct(
        #[Set(EngineInterface::class)] private readonly Map $engines
    ) {}
}
```

`Map` は読み取り専用・遅延評価で、`$engines['gas']` にアクセスした時点で初めてそのエントリが生成されます。収集順は 3 節の合成仕様に従います。Symfony の tagged services、Laravel の `tag()` / `tagged()` が対応物ですが、どちらもタグは文字列で、Ray.Di ではキーが型(インターフェース)です。

このほか、メソッド注入と `#[Inject(optional: true)]`(束縛がなければ黙ってスキップ)、`#[PostConstruct]`(全注入完了後の初期化フック)、assisted injection(メソッド実行時に実行時引数と注入引数を混在: `find($name, #[Inject] ?FinderInterface $finder = null)`)があります。

## 8. 診断とテスト — 失敗を早く、合成結果を見えるように

**束縛時。** 2 節で述べたとおり、実装関係の誤りやクラス名の誤りは `configure()` 実行時に例外になります。

**解決時。** 束縛漏れの `Unbound` は、どの依存キーが・どのファイルのどのパラメータで要求され・そこへ至る依存チェーンが何だったかを連鎖で示します(`demo/12-dependency-chain-error-message.php` の実行出力)。

```
exception 'Ray\Di\Exception\Unbound' with message ''EInterface-''
- 'EInterface-' in demo/chain-error/D.php:7 ($e)
- 'D-' in demo/chain-error/C.php:7 ($d)
- 'C-' in demo/chain-error/B.php:7 ($c)
- 'B-' in demo/chain-error/A.php:7 ($b)
```

「A を作れなかったのは B → C → D と辿った先の `EInterface` が未束縛だから」という因果が、要求元の行番号とパラメータ名付きで例外だけから読めます。

**テスト時。** 差し替えは合成の語彙(3 節)がそのまま使えます。

```php
$module = new AppModule();
$module->override(new FakeDbModule());  // テスト用束縛が必ず勝つ
```

まだ実装のないインターフェースや、テストで無効化したい依存には `toNull()` があります。

```php
$this->bind(MailerInterface::class)->toNull();  // Null Object 実装を自動生成
```

インターフェースから「何もしない実装」を生成して束縛するので、手書きのスタブなしでグラフを完成させられます。

**レビュー時。** モジュールが最終的にどんな束縛の集合に合成されたかは、visitor でスナップショットにできます(README 記載の API)。

```php
$bindings = new Bindings();
$module->accept($bindings);
$markdown = $bindings->toMarkdown();  // または toHtml()
```

各束縛がどのモジュール由来か(provenance)と衝突の履歴も記録されるため、「この束縛はどこで決まったのか」を成果物としてレビューに載せられます。この領域の比較対象は Symfony が厚く、`debug:container` / `lint:container` は同じ問いにコマンドラインで答えるものです。Laravel と PHP-DI の診断は実行時例外が中心です。

## 9. トレードオフ — Ray.Di が選ばなかったもの

ここまでの各判断には代償があります。逆側から並べます。

**コンパイル時検証。** Symfony はコンテナのコンパイル時に未解決の参照を検出します。CI でビルドが落ちれば、アプリケーションを 1 行も実行せずに設定の誤りが分かります。Ray.Di の素の構成では、束縛の型検証(2 節)を除けば誤りが見つかるのは最初の `getInstance()` です。ray/compiler のコンパイルを CI に組み込めば同等の事前検証になりますが、既定の workflow ではありません。

**設定の非コード表現。** YAML / XML には、PHP を実行せずに機械処理できる・差分レビューがコードより読みやすい場合がある・非開発者にも触れる、という実利があります。また Symfony の `autoconfigure` やタグによる規約ベースの一括登録に相当するものを Ray.Di は持たず、束縛は原則すべて明示です。登録が大量で規約的なアプリケーションでは、この明示性は冗長さとして現れます。

**透明な遅延生成。** Symfony の lazy services や PHP-DI の `#[Injectable(lazy: true)]` のような、本物そっくりのプロキシで生成を遅らせる仕組みはありません。Ray.Di で遅延は `ProviderInterface` を注入して `get()` を呼ぶ、という形でコードに現れます。遅延を型として見えるようにする立場ですが、既存コードを変えずに重い依存を遅延化したい場合には相手方式が短く済みます。

**フレームワーク統合。** Symfony / Laravel のコンテナは、自フレームワークのイベント・コンソール・キュー・設定と結線済みで、その世界の中では設定ゼロで機能します。Ray.Di はフレームワーク非依存で、深い統合は BEAR.Sunday が担います。PSR-11 を前提にする統合点(ミドルウェアやブリッジ)へは `getInstance()` に委譲する薄いアダプタで接続できます。既に Symfony / Laravel の上にいるなら、そのコンテナを使うのが摩擦最小です。

## 10. まとめ

Ray.Di と比較対象の違いは、機能の有無の一覧より 1 つの設計判断に還元できます。**コンテナを「文字列 ID で値を取り出すサービスレジストリ」ではなく「型からオブジェクトグラフを構築する injector」として設計した**ことです。束縛のキーが型であること(1 節)、束縛が型検査される式であること(2 節)、合成の意味論が仕様であること(3 節)、横断的関心事も束縛の言語で宣言すること(4 節)、既定が Prototype であること(5 節)は、すべてその帰結です。

- 束縛を静的解析の管理下に置きたい、モジュールを配布・合成してもどの束縛が勝つかが仕様で保証されてほしい、AOP を DI と同じ場所で宣言したい、フレームワークに依存したくない — この優先順位なら Ray.Di の判断が効きます。
- コンテナを部品としてコードに配る設計を前提にする、設定を非コードで管理したい、コンパイル時検証を最優先にしたい、既存フレームワークのエコシステムの内側にいる — この優先順位なら Symfony DI・PHP-DI・各フレームワークのコンテナの判断が合理的です。

一次資料はこのリポジトリにあります。動く最小例は `demo/`、合成の意味論の仕様は `tests/di/README.md` と `tests/di/ModuleCompositionTest.php`、利用者向けマニュアルは [ray-di.github.io](https://ray-di.github.io) です。
