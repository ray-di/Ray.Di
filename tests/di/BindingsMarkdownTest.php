<?php

declare(strict_types=1);

namespace Ray\Di;

use Closure;
use PHPUnit\Framework\TestCase;
use Ray\Aop\Matcher;
use Ray\Aop\NullInterceptor;
use Ray\Aop\Pointcut;
use RuntimeException;

use function assert;
use function clearstatcache;
use function file_exists;
use function file_get_contents;
use function file_put_contents;
use function filemtime;
use function glob;
use function is_dir;
use function is_string;
use function mkdir;
use function rmdir;
use function sys_get_temp_dir;
use function time;
use function touch;
use function uniqid;
use function unlink;

class BindingsMarkdownTest extends TestCase
{
    private string $classDir;

    protected function setUp(): void
    {
        $this->classDir = sys_get_temp_dir() . '/ray-di-bindings-md-' . uniqid('', true);
        if (! mkdir($this->classDir) && ! is_dir($this->classDir)) {
            throw new RuntimeException('Cannot create ' . $this->classDir);
        }
    }

    protected function tearDown(): void
    {
        // remove the whole test-owned dir: bindings.md and any generated proxies
        foreach (glob($this->classDir . '/*') ?: [] as $artifact) {
            unlink($artifact);
        }

        if (is_dir($this->classDir)) {
            rmdir($this->classDir);
        }
    }

    public function testWriterEmitsBindingsMarkdown(): void
    {
        $writer = new BindingsMarkdown();
        $writer((new FakeLogStringModule())->getContainer(), $this->classDir);

        $markdown = file_get_contents($this->classDir . '/bindings.md');
        assert(is_string($markdown));

        // title + summary counts, then Bindings first
        $this->assertStringStartsWith(
            "# Ray.Di bindings\n\n9 bindings · 1 modules · 0 replaced · 0 discarded\n\n## Bindings\n\n",
            $markdown,
        );
        // bindings list the resolved target
        $this->assertStringContainsString(FakeAopInterface::class . '- => (dependency) ' . FakeAop::class, $markdown);
        // modules, sorted, with each module's binding count, then provenance
        $this->assertStringContainsString(
            "## Modules\n\n"
            . '- ' . FakeLogStringModule::class . " (9)\n\n## Provenance",
            $markdown,
        );
        // provenance names the module that bound it
        $this->assertStringContainsString('@' . FakeLogStringModule::class, $markdown);
        // the signature trailer makes the file its own cache
        $this->assertMatchesRegularExpression('#\n<!-- signature: [0-9a-f]{64} -->\n$#', $markdown);
    }

    /**
     * The summary counts replaced (last-write-wins rebind) and discarded
     * (merge collision, incoming dropped) bindings.
     */
    public function testSummaryCountsReplacedAndDiscarded(): void
    {
        $writer = new BindingsMarkdown();
        $writer((new FakeBindingLogModule(new FakeBindingLogInnerModule()))->getContainer(), $this->classDir);

        $markdown = file_get_contents($this->classDir . '/bindings.md');
        assert(is_string($markdown));

        $this->assertStringContainsString('2 bindings · 2 modules · 1 replaced · 1 discarded', $markdown);
    }

    /** A closure binding is rendered without serializing the container. */
    public function testClosureBindingIsWrittenToMarkdown(): void
    {
        $module = new FakeClosureBindModule();
        (new BindingsMarkdown())($module->getContainer(), $this->classDir);
        $injector = new Injector($module, $this->classDir);

        $this->assertInstanceOf(Closure::class, $injector->getInstance('', 'callback'));
        $markdown = file_get_contents($this->classDir . '/bindings.md');
        assert(is_string($markdown));
        $this->assertStringContainsString('-callback => (object) Closure', $markdown);
    }

    /**
     * A concrete class bound to itself (untargeted) is collapsed to
     * '(untargeted)' in the Bindings section, matching the provenance label so
     * the resolved bindings and the provenance agree.
     */
    public function testUntargetedBindingIsCollapsedInTheBindingsSection(): void
    {
        $module = new class extends AbstractModule {
            protected function configure(): void
            {
                $this->bind(FakeEngine::class);
            }
        };
        (new BindingsMarkdown())($module->getContainer(), $this->classDir);

        $markdown = file_get_contents($this->classDir . '/bindings.md');
        assert(is_string($markdown));

        $this->assertStringContainsString(FakeEngine::class . '- => (untargeted)', $markdown);
    }

    /** An explicitly owned script dir receives the snapshot at composition time. */
    public function testInjectorWritesBindingsMarkdownToExplicitDir(): void
    {
        new Injector(new FakeToBindModule(), $this->classDir);

        $markdown = file_get_contents($this->classDir . '/bindings.md');
        assert(is_string($markdown));
        $this->assertStringContainsString(FakeRobotInterface::class . '- => (dependency) ' . FakeRobot::class, $markdown);
        // the Injector's own built-in binding is part of the composed truth
        $this->assertStringContainsString(InjectorInterface::class . '-', $markdown);
    }

    /** The shared sys_get_temp_dir() fallback is not application-owned. */
    public function testInjectorDoesNotWriteBindingsMarkdownToSharedTempDir(): void
    {
        // Never touch the shared file: assert the Injector leaves its existence
        // unchanged rather than deleting a path another process may own.
        $file = sys_get_temp_dir() . '/bindings.md';
        $existedBefore = file_exists($file);
        $contentsBefore = $existedBefore ? file_get_contents($file) : null;
        $mtimeBefore = $existedBefore ? filemtime($file) : null;

        new Injector(new FakeToBindModule());

        clearstatcache();
        $this->assertSame($existedBefore, file_exists($file));
        if ($existedBefore) {
            $this->assertSame($contentsBefore, file_get_contents($file));
            $this->assertSame($mtimeBefore, filemtime($file));
        }
    }

    /** Resolution performs no diagnostics I/O; the snapshot is composition-time only. */
    public function testGetInstanceDoesNotTouchBindingsMarkdown(): void
    {
        $injector = new Injector(new FakeToBindModule(), $this->classDir);
        $file = $this->classDir . '/bindings.md';
        $past = time() - 3600;
        touch($file, $past);

        $injector->getInstance(FakeRobotInterface::class);

        clearstatcache();
        $this->assertSame($past, filemtime($file));
    }

    public function testWriterUsesThePublicRenderer(): void
    {
        $container = (new FakeLogStringModule())->getContainer();
        $writer = new BindingsMarkdown();
        $rendered = $writer->render($container);

        $writer($container, $this->classDir);

        $markdown = file_get_contents($this->classDir . '/bindings.md');
        assert(is_string($markdown));
        $this->assertStringStartsWith($rendered, $markdown);
    }

    /** An unchanged binding surface reuses the existing markdown file. */
    public function testUnchangedBindingsSkipRewrite(): void
    {
        $container = (new FakeLogStringModule())->getContainer();
        $writer = new BindingsMarkdown();
        $writer($container, $this->classDir);
        $file = $this->classDir . '/bindings.md';
        $past = time() - 3600;
        touch($file, $past);

        $writer($container, $this->classDir);

        clearstatcache();
        $this->assertSame($past, filemtime($file));
    }

    /** A changed resolved binding's type invalidates the cached markdown. */
    public function testBindingTypeChangeInvalidatesSignatureCache(): void
    {
        $container = new Container();
        (new Bind($container, '', self::class))->annotatedWith('value')->toInstance(1);
        $writer = new BindingsMarkdown();
        $writer($container, $this->classDir);

        (new Bind($container, '', self::class))->annotatedWith('value')->toInstance('changed');
        $writer($container, $this->classDir);

        $markdown = file_get_contents($this->classDir . '/bindings.md');
        assert(is_string($markdown));
        $this->assertStringContainsString('-value => (string)', $markdown);
    }

    /**
     * A scalar toInstance() value never appears in the Bindings section or
     * its own provenance ("bind") event; only the type tag does.
     */
    public function testScalarInstanceValueIsRedactedInBindingsAndProvenance(): void
    {
        $container = new Container();
        (new Bind($container, '', self::class))->annotatedWith('secret')->toInstance('fake-secret-value-not-real-9f2b1c');
        $writer = new BindingsMarkdown();
        $writer($container, $this->classDir);

        $markdown = file_get_contents($this->classDir . '/bindings.md');
        assert(is_string($markdown));
        $this->assertStringContainsString('-secret => (string)', $markdown);
        $this->assertStringContainsString('bind    -secret => (string) @' . self::class, $markdown);
        $this->assertStringNotContainsString('fake-secret-value-not-real-9f2b1c', $markdown);
    }

    /** A same-module rebind (replace) never renders the discarded scalar's value. */
    public function testReplacedScalarValueIsRedactedInProvenance(): void
    {
        $container = new Container();
        (new Bind($container, '', self::class))->annotatedWith('secret')->toInstance('old-fake-secret-value');
        (new Bind($container, '', self::class))->annotatedWith('secret')->toInstance('new-fake-secret-value');
        $writer = new BindingsMarkdown();
        $writer($container, $this->classDir);

        $markdown = file_get_contents($this->classDir . '/bindings.md');
        assert(is_string($markdown));
        $this->assertStringContainsString(
            'replace -secret => (string) @' . self::class . ' (replaced (string) @' . self::class . ')',
            $markdown,
        );
        $this->assertStringNotContainsString('old-fake-secret-value', $markdown);
        $this->assertStringNotContainsString('new-fake-secret-value', $markdown);
    }

    /** A merge collision never renders either side's scalar value (kept or discarded). */
    public function testKeptAndDiscardedScalarValuesAreRedactedOnMerge(): void
    {
        $kept = new Container();
        (new Bind($kept, '', 'KeptModule'))->annotatedWith('secret')->toInstance('kept-fake-secret-value');
        $incoming = new Container();
        (new Bind($incoming, '', 'IncomingModule'))->annotatedWith('secret')->toInstance('discarded-fake-secret-value');

        $kept->merge($incoming);

        $writer = new BindingsMarkdown();
        $writer($kept, $this->classDir);

        $markdown = file_get_contents($this->classDir . '/bindings.md');
        assert(is_string($markdown));
        $this->assertStringContainsString(
            'keep    -secret => (string) @KeptModule (discarded (string) @IncomingModule)',
            $markdown,
        );
        $this->assertStringNotContainsString('kept-fake-secret-value', $markdown);
        $this->assertStringNotContainsString('discarded-fake-secret-value', $markdown);
    }

    /** Provenance-only changes intentionally retain the cached markdown. */
    public function testProvenanceChangeDoesNotInvalidateSignatureCache(): void
    {
        $first = new Container();
        (new Bind($first, '', 'FirstModule'))->annotatedWith('value')->toInstance(1);
        $second = new Container();
        (new Bind($second, '', 'SecondModule'))->annotatedWith('value')->toInstance(1);
        $writer = new BindingsMarkdown();
        $writer($first, $this->classDir);

        $writer($second, $this->classDir);

        $markdown = file_get_contents($this->classDir . '/bindings.md');
        assert(is_string($markdown));
        $this->assertStringContainsString('@FirstModule', $markdown);
        $this->assertStringNotContainsString('@SecondModule', $markdown);
    }

    /** Matcher runtime state is not part of the declarative pointcut signature. */
    public function testMatcherRuntimeStateDoesNotInvalidateSignatureCache(): void
    {
        $container = new Container();
        (new Bind($container, FakeAopInterface::class, self::class))->to(FakeAop::class);
        $classMatcher = new FakeCountingMatcher();
        $container->addPointcut(new Pointcut($classMatcher, (new Matcher())->any(), [FakeDoubleInterceptor::class]));
        $writer = new BindingsMarkdown();
        $writer($container, $this->classDir);
        $matches = $classMatcher->matches;
        $file = $this->classDir . '/bindings.md';
        $past = time() - 3600;
        touch($file, $past);

        $writer($container, $this->classDir);

        $this->assertSame($matches, $classMatcher->matches);
        clearstatcache();
        $this->assertSame($past, filemtime($file));
    }

    /** A file without the signature trailer (hand-edited or stale) is rewritten. */
    public function testFileWithoutSignatureIsRewritten(): void
    {
        file_put_contents($this->classDir . '/bindings.md', 'stale');

        (new BindingsMarkdown())((new FakeLogStringModule())->getContainer(), $this->classDir);

        $markdown = file_get_contents($this->classDir . '/bindings.md');
        assert(is_string($markdown));
        $this->assertStringStartsWith('# Ray.Di bindings', $markdown);
    }

    /** An interceptor bound as an instance renders and signs by its class. */
    public function testInterceptorInstanceIsPartOfTheSignature(): void
    {
        $container = new Container();
        (new Bind($container, FakeAopInterface::class, self::class))->to(FakeAop::class);
        $container->addPointcut(new Pointcut((new Matcher())->any(), (new Matcher())->any(), [new NullInterceptor()]));

        (new BindingsMarkdown())($container, $this->classDir);

        $markdown = file_get_contents($this->classDir . '/bindings.md');
        assert(is_string($markdown));
        $this->assertStringContainsString(NullInterceptor::class, $markdown);
    }

    /** The signature is order-independent: identical bindings in any insertion order reuse the file. */
    public function testSignatureIsBindingOrderIndependent(): void
    {
        $a = new Container();
        (new Bind($a, '', self::class))->annotatedWith('x')->toInstance(1);
        (new Bind($a, '', self::class))->annotatedWith('y')->toInstance(2);

        $b = new Container();
        (new Bind($b, '', self::class))->annotatedWith('y')->toInstance(2);
        (new Bind($b, '', self::class))->annotatedWith('x')->toInstance(1);

        $writer = new BindingsMarkdown();
        $writer($a, $this->classDir);
        $file = $this->classDir . '/bindings.md';
        $past = time() - 3600;
        touch($file, $past);

        $writer($b, $this->classDir); // same bindings, reversed insertion order

        clearstatcache();
        $this->assertSame($past, filemtime($file)); // reused
    }

    /** The Modules section is sorted alphabetically by module name. */
    public function testModulesSectionIsSortedAlphabetically(): void
    {
        $container = new Container();
        (new Bind($container, '', 'ZZZMod'))->annotatedWith('a')->toInstance(1);
        (new Bind($container, '', 'AAAMod'))->annotatedWith('b')->toInstance(2);

        (new BindingsMarkdown())($container, $this->classDir);

        $markdown = file_get_contents($this->classDir . '/bindings.md');
        assert(is_string($markdown));
        $this->assertStringContainsString("## Modules\n\n- AAAMod (1)\n- ZZZMod (1)\n", $markdown);
    }

    /** A signature trailer that is not at end-of-file is treated as a cache miss. */
    public function testSignatureTrailerMustBeAtEndOfFile(): void
    {
        $container = (new FakeLogStringModule())->getContainer();
        $writer = new BindingsMarkdown();
        $writer($container, $this->classDir);
        $file = $this->classDir . '/bindings.md';
        $markdown = file_get_contents($file);
        assert(is_string($markdown));
        file_put_contents($file, $markdown . "trailing junk after trailer\n");

        $past = time() - 3600;
        touch($file, $past);

        $writer($container, $this->classDir); // trailer no longer at end → cache miss

        clearstatcache();
        $this->assertNotSame($past, filemtime($file)); // rewritten
    }

    /** Each pointcut's interceptor list is part of the signature. */
    public function testPointcutInterceptorIsPartOfSignature(): void
    {
        $a = new Container();
        (new Bind($a, FakeAopInterface::class, self::class))->to(FakeAop::class);
        $a->addPointcut(new Pointcut((new Matcher())->any(), (new Matcher())->any(), [new NullInterceptor()]));
        $a->addPointcut(new Pointcut((new Matcher())->any(), (new Matcher())->startsWith('return'), [new NullInterceptor()]));

        $b = new Container();
        (new Bind($b, FakeAopInterface::class, self::class))->to(FakeAop::class);
        $b->addPointcut(new Pointcut((new Matcher())->any(), (new Matcher())->any(), [new NullInterceptor()]));
        $b->addPointcut(new Pointcut((new Matcher())->any(), (new Matcher())->startsWith('return'), [new FakeDoubleInterceptor()]));

        $writer = new BindingsMarkdown();
        $writer($a, $this->classDir);
        $file = $this->classDir . '/bindings.md';
        $past = time() - 3600;
        touch($file, $past);

        $writer($b, $this->classDir); // second pointcut's interceptor differs

        clearstatcache();
        $this->assertNotSame($past, filemtime($file)); // rewritten
    }

    /** The binding index (interface-name) is part of the signature. */
    public function testBindingIndexIsPartOfSignature(): void
    {
        $a = new Container();
        (new Bind($a, FakeRobotInterface::class, self::class))->annotatedWith('v')->toInstance(1);

        $b = new Container();
        (new Bind($b, FakeEngineInterface::class, self::class))->annotatedWith('v')->toInstance(1); // same value, different interface

        $writer = new BindingsMarkdown();
        $writer($a, $this->classDir);
        $file = $this->classDir . '/bindings.md';
        $past = time() - 3600;
        touch($file, $past);

        $writer($b, $this->classDir); // different index → different signature

        clearstatcache();
        $this->assertNotSame($past, filemtime($file)); // rewritten
    }
}
