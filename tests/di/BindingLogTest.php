<?php

declare(strict_types=1);

namespace Ray\Di;

use PHPUnit\Framework\TestCase;
use Ray\Di\MultiBinding\MultiBindings;
use ReflectionProperty;

use function array_filter;
use function assert;
use function serialize;
use function unserialize;

/**
 * Pins the binding provenance & collision history contract
 *
 * Module composition merges silently: bind() overwrites (last wins), while
 * install() and constructor chaining merge with `+=` (existing wins, incoming
 * silently discarded). PR #319 proved such discards are invisible to both
 * tests and humans. The BindingLog records every composition write, so "who
 * bound X, who was overwritten, who was discarded, in which module" is
 * observable. These tests are the specification of that record: the complete
 * log text, the provenance map, and the event objects.
 *
 * The golden fixture `new FakeBindingLogModule(new FakeBindingLogInnerModule())`
 * exercises every event type but move: an install() adoption, a bind, a
 * same-module replace, and — because the constructor-chained module merges
 * after configure() — a keep/discard collision.
 */
class BindingLogTest extends TestCase
{
    /**
     * The complete log: one line per composition write, in write order.
     *
     * The installed module's bind is adopted at install()-merge with its own
     * module as source; the two engine binds are a bind then a same-module
     * replace; the chained module's bind arrives last (merged after
     * configure()) and loses to the already-installed binding — a keep.
     */
    public function testLogRecordsEveryCompositionWrite(): void
    {
        $expected = <<<'LOG'
bind    Ray\Di\FakeRobotInterface- => (dependency) Ray\Di\FakeRobot2 @Ray\Di\FakeBindingLogInstalledModule
bind    Ray\Di\FakeEngineInterface- => (dependency) Ray\Di\FakeEngine @Ray\Di\FakeBindingLogModule
replace Ray\Di\FakeEngineInterface- => (dependency) Ray\Di\FakeEngine2 @Ray\Di\FakeBindingLogModule (replaced (dependency) Ray\Di\FakeEngine @Ray\Di\FakeBindingLogModule)
bind    Ray\Di\FakeRobotInterface- => (dependency) Ray\Di\FakeRobot @Ray\Di\FakeBindingLogInnerModule
keep    Ray\Di\FakeRobotInterface- => (dependency) Ray\Di\FakeRobot2 @Ray\Di\FakeBindingLogInstalledModule (discarded (dependency) Ray\Di\FakeRobot @Ray\Di\FakeBindingLogInnerModule)
LOG;

        $this->assertSame($expected, (string) $this->composeGoldenLog());
    }

    /**
     * getSources() maps each index to the module owning the *current* binding:
     * the installed module for the robot (it beat the chained module), the
     * composing module for the engine (its replace was the last write).
     */
    public function testSourcesRecordTheWinningModulePerIndex(): void
    {
        $log = $this->composeGoldenLog();

        $this->assertSame([
            FakeRobotInterface::class . '-' => FakeBindingLogInstalledModule::class,
            FakeEngineInterface::class . '-' => FakeBindingLogModule::class,
        ], $log->getSources());
        $this->assertSame(FakeBindingLogInstalledModule::class, $log->getSource(FakeRobotInterface::class . '-'));
        $this->assertNull($log->getSource(FakeCarInterface::class . '-'));
    }

    /**
     * A keep event names both sides of the collision: the surviving binding
     * with its owning module, and the discarded incoming binding with its
     * owning module — the exact information PR #319-class bugs erase.
     */
    public function testKeepEventCarriesBothSidesOfTheCollision(): void
    {
        $events = $this->composeGoldenLog()->getEvents();

        $this->assertCount(5, $events);
        $keep = $events[4];
        $this->assertSame(BindingEvent::KEEP, $keep->type);
        $this->assertSame(FakeRobotInterface::class . '-', $keep->index);
        $this->assertSame('(dependency) ' . FakeRobot2::class, $keep->dependency);
        $this->assertSame(FakeBindingLogInstalledModule::class, $keep->source);
        $this->assertSame('(dependency) ' . FakeRobot::class, $keep->discarded);
        $this->assertSame(FakeBindingLogInnerModule::class, $keep->discardedSource);
        $this->assertNull($keep->movedFrom);
    }

    /**
     * bind() twice on the same index inside one module is a replace whose
     * discarded side belongs to that same module — self-shadowing is recorded
     * as faithfully as cross-module shadowing.
     */
    public function testReplaceWithinOneModuleAttributesDiscardedSourceToThatModule(): void
    {
        $events = $this->composeGoldenLog()->getEvents();

        $replace = $events[2];
        $this->assertSame(BindingEvent::REPLACE, $replace->type);
        $this->assertSame(FakeEngineInterface::class . '-', $replace->index);
        $this->assertSame('(dependency) ' . FakeEngine2::class, $replace->dependency);
        $this->assertSame(FakeBindingLogModule::class, $replace->source);
        $this->assertSame('(dependency) ' . FakeEngine::class, $replace->discarded);
        $this->assertSame(FakeBindingLogModule::class, $replace->discardedSource);
    }

    /**
     * An untargeted (or self-bound) class lands at index '{class}-' with the
     * same class as its dependency; the string form collapses the repeat to
     * '(untargeted)'. A targeted binding, whose interface differs from the
     * bound class, is shown in full.
     */
    public function testUntargetedBindingCollapsesTheRepeatedClassInString(): void
    {
        $untargeted = new BindingEvent(BindingEvent::BIND, FakeRobot::class . '-', '(dependency) ' . FakeRobot::class, 'M');
        $this->assertSame('bind    ' . FakeRobot::class . '- => (untargeted) @M', (string) $untargeted);

        $targeted = new BindingEvent(BindingEvent::BIND, FakeRobotInterface::class . '-', '(dependency) ' . FakeRobot::class, 'M');
        $this->assertSame('bind    ' . FakeRobotInterface::class . '- => (dependency) ' . FakeRobot::class . ' @M', (string) $targeted);
    }

    /**
     * A null-object binding (toNull) has an empty dependency string form; the
     * event labels it '(null object)' so the reader sees a bound null object
     * rather than a blank target.
     */
    public function testNullObjectBindingShowsANullObjectMarkerInString(): void
    {
        $nullObject = new BindingEvent(BindingEvent::BIND, FakeRobotInterface::class . '-', '', 'M');
        $this->assertSame('bind    ' . FakeRobotInterface::class . '- => (null object) @M', (string) $nullObject);
    }

    /**
     * rename() is a move: the binding leaves its old index for the new one,
     * and the log both records the move and transfers provenance — the moved
     * binding still belongs to the module that bound it, not to the renamer.
     */
    public function testRenameRecordsMoveEventAndTransfersProvenance(): void
    {
        $module = new class (new FakeToBindModule()) extends AbstractModule {
            protected function configure(): void
            {
                $this->rename(FakeRobotInterface::class, 'renamed');
            }
        };
        $log = $module->getContainer()->log;

        $events = $log->getEvents();
        $this->assertCount(2, $events);
        $move = $events[1];
        $this->assertSame(BindingEvent::MOVE, $move->type);
        $this->assertSame(FakeRobotInterface::class . '-renamed', $move->index);
        $this->assertSame(FakeRobotInterface::class . '-', $move->movedFrom);
        $this->assertSame(FakeToBindModule::class, $move->source);
        $this->assertSame(
            'move    Ray\Di\FakeRobotInterface- => Ray\Di\FakeRobotInterface-renamed @Ray\Di\FakeToBindModule',
            (string) $move
        );
        $this->assertSame(FakeToBindModule::class, $log->getSource(FakeRobotInterface::class . '-renamed'));
        $this->assertNull($log->getSource(FakeRobotInterface::class . '-'));
    }

    /**
     * The log is composition-time state: it is excluded from serialization,
     * and a revived container starts with an empty log that still works.
     * Writes after unserialize() are attributed 'unknown' on both sides —
     * the writer identity and the provenance map did not survive, and the
     * log says so instead of guessing.
     */
    public function testUnserializedContainerStartsWithEmptyUsableLog(): void
    {
        $container = (new FakeBindingLogInnerModule())->getContainer();
        $this->assertNotSame('', (string) $container->log);

        $revived = unserialize(serialize($container));
        assert($revived instanceof Container);
        $log = $revived->log;
        $this->assertSame('', (string) $log);
        $this->assertSame([], $log->getEvents());
        $this->assertSame([], $log->getSources());
        $this->assertNull($log->getSource(FakeRobotInterface::class . '-'));

        (new Bind($revived, FakeRobotInterface::class))->to(FakeRobot2::class);
        // the lazily created log is a live instance, not a per-call copy
        $this->assertSame($log, $revived->log);
        $events = $revived->log->getEvents();
        $this->assertCount(1, $events);
        $replace = $events[0];
        $this->assertSame(BindingEvent::REPLACE, $replace->type);
        $this->assertSame('(dependency) ' . FakeRobot2::class, $replace->dependency);
        $this->assertSame('unknown', $replace->source);
        $this->assertSame('(dependency) ' . FakeRobot::class, $replace->discarded);
        $this->assertSame('unknown', $replace->discardedSource);
    }

    /**
     * The Injector's built-in InjectorInterface binding (and everything the
     * Injector writes after composition, e.g. JIT bindings) is attributed to
     * Ray\Di\Injector, not to the user module it happens to be merged into.
     */
    public function testInjectorBuiltinBindingAttributesToInjector(): void
    {
        $module = new FakeBindingLogInnerModule();
        new Injector($module, __DIR__ . '/tmp');

        $log = $module->getContainer()->log;
        $this->assertSame(Injector::class, $log->getSource(InjectorInterface::class . '-'));
    }

    /**
     * A cached (unserialized) injector keeps that promise: __wakeup()
     * re-stamps the container source, so a JIT binding performed after
     * unserialize() is attributed to Ray\Di\Injector, not 'unknown'.
     */
    public function testUnserializedInjectorAttributesJitBindingToInjector(): void
    {
        $injector = unserialize(serialize(new Injector(null, __DIR__ . '/tmp')));
        assert($injector instanceof Injector);
        $this->expectUserDeprecationMessage(
            'Just-in-time binding of unbound concrete class "' . FakeEngine::class . '" is deprecated. Bind it explicitly: JIT resolution is unavailable under CompiledInjector and is not recorded in bindings.md.',
        );
        $injector->getInstance(FakeEngine::class); // JIT binding after wakeup

        $container = (new ReflectionProperty(Injector::class, 'container'))->getValue($injector);
        assert($container instanceof Container);

        $this->assertSame(Injector::class, $container->log->getSource(FakeEngine::class . '-'));
    }

    /**
     * The MultiBindings container binding is infrastructure that
     * MultiBinder installs alongside the real map/set entries; the entries
     * themselves bypass the log (they are written straight to the store), so
     * logging only the MultiBindings binding produces noise that the
     * docblock promises is absent. Assert that promise holds: no event of
     * any kind (bind/replace/keep/move) carries the MultiBindings index, and
     * the provenance map has no entry for it.
     */
    public function testMultiBindingsIndexIsExcludedFromTheLog(): void
    {
        $module = new class extends AbstractModule {
            protected function configure(): void
            {
                MultiBinder::newInstance($this, FakeEngineInterface::class)
                    ->addBinding('one')->to(FakeEngine::class);
                MultiBinder::newInstance($this, FakeRobotInterface::class)
                    ->addBinding('robot')->to(FakeRobot::class);
            }
        };
        $log = $module->getContainer()->log;
        $index = MultiBindings::class . '-' . Name::ANY;

        foreach ($log->getEvents() as $event) {
            $this->assertNotSame($index, $event->index, 'MultiBindings index must not appear in any event: ' . $event);
        }

        $this->assertArrayNotHasKey($index, $log->getSources());
        $this->assertNull($log->getSource($index));
    }

    /**
     * The same exclusion holds across a module merge: when sibling modules
     * each install their own MultiBinder, the MultiBindings binding collides
     * on the merge and would otherwise surface as a keep event. The keep
     * event is the noise the #340 fix made invisible by re-pointing the
     * binding after the merge; the log must not record it in the first place.
     */
    public function testMultiBindingsIndexEmitsNoKeepEventOnMerge(): void
    {
        $module = new class extends AbstractModule {
            protected function configure(): void
            {
                $this->install(new class extends AbstractModule {
                    protected function configure(): void
                    {
                        MultiBinder::newInstance($this, FakeEngineInterface::class)
                            ->addBinding('first')->to(FakeEngine::class);
                    }
                });
                $this->install(new class extends AbstractModule {
                    protected function configure(): void
                    {
                        MultiBinder::newInstance($this, FakeEngineInterface::class)
                            ->addBinding('second')->to(FakeEngine2::class);
                        MultiBinder::newInstance($this, FakeRobotInterface::class)
                            ->addBinding('robot')->to(FakeRobot::class);
                    }
                });
            }
        };
        $log = $module->getContainer()->log;
        $index = MultiBindings::class . '-' . Name::ANY;

        $keepEvents = array_filter(
            $log->getEvents(),
            static fn (BindingEvent $event): bool => $event->type === BindingEvent::KEEP && $event->index === $index
        );
        $this->assertSame([], $keepEvents, 'No keep event for the MultiBindings index after merge');

        foreach ($log->getEvents() as $event) {
            $this->assertNotSame($index, $event->index, 'MultiBindings index must not appear in any event: ' . $event);
        }

        $this->assertArrayNotHasKey($index, $log->getSources());
    }

    private function composeGoldenLog(): BindingLog
    {
        return (new FakeBindingLogModule(new FakeBindingLogInnerModule()))->getContainer()->log;
    }
}
