<?php

declare(strict_types=1);

namespace Ray\Di;

use PHPUnit\Framework\TestCase;

use function array_keys;
use function assert;
use function iterator_to_array;

/**
 * Pins the composition contract for Ray.Di modules
 *
 * These tests are the specification of "which binding wins". A change that
 * flips any assertion here is a backward-compatibility break of module
 * composition semantics — fix the change, not the test.
 *
 * For the same {interface, name} key:
 *
 *   own bind()        vs constructor-chained module -> own bind()
 *   own install()     vs constructor-chained module -> own install()
 *   bind()            vs install() (either order)   -> bind()
 *   first install()   vs second install()           -> first install()
 *   first install's multibinding entry vs second install's, same Map key
 *                                                   -> first install's entry
 *   outer chain       vs inner chain                -> outer
 *   override() target vs anything already bound     -> override() target
 *
 * Mechanism: bind() registers by assignment (last write wins); install() and
 * constructor chaining merge with `+=` (existing entries win); configure()
 * runs before the constructor-chained module is merged. AOP pointcuts and
 * multibindings append in the same order, so "earlier declared" also means
 * "outermost interceptor" and "first in a Map".
 *
 * BEAR.Sunday context modules (`new ProdModule(new HalModule(new AppModule))`)
 * rely on this contract: the left (outer) context module must override the
 * right one.
 */
class ModuleCompositionTest extends TestCase
{
    /**
     * A binding declared directly with bind() must override the same binding
     * coming from the constructor-chained module.
     */
    public function testOwnBindOverridesConstructorChainedBinding(): void
    {
        $module = new class (new FakeToBindModule()) extends AbstractModule {
            protected function configure(): void
            {
                $this->bind(FakeRobotInterface::class)->to(FakeRobot2::class);
            }
        };

        $instance = $module->getContainer()->getInstance(FakeRobotInterface::class, Name::ANY);

        $this->assertInstanceOf(FakeRobot2::class, $instance);
    }

    /**
     * A binding introduced by install() inside configure() must override the
     * same binding coming from the constructor-chained module. ProdModule-style
     * context modules bind exclusively through install(), so if the chained
     * module won here, a prod context would silently run with dev bindings.
     */
    public function testInstallOverridesConstructorChainedBinding(): void
    {
        $module = new class (new FakeToBindModule()) extends AbstractModule {
            protected function configure(): void
            {
                $this->install(new class extends AbstractModule {
                    protected function configure(): void
                    {
                        $this->bind(FakeRobotInterface::class)->to(FakeRobot2::class);
                    }
                });
            }
        };

        $instance = $module->getContainer()->getInstance(FakeRobotInterface::class, Name::ANY);

        $this->assertInstanceOf(FakeRobot2::class, $instance);
    }

    /**
     * bind() overrides a binding installed earlier in the same configure().
     * bind() is assertive: it always registers, no matter what is already there.
     */
    public function testBindOverridesEarlierInstalledBinding(): void
    {
        $module = new class extends AbstractModule {
            protected function configure(): void
            {
                $this->install(new FakeToBindModule()); // FakeRobotInterface -> FakeRobot
                $this->bind(FakeRobotInterface::class)->to(FakeRobot2::class);
            }
        };

        $instance = $module->getContainer()->getInstance(FakeRobotInterface::class, Name::ANY);

        $this->assertInstanceOf(FakeRobot2::class, $instance);
    }

    /**
     * install() does not override a binding declared earlier with bind().
     * install() is polite: it only fills keys that are not yet bound.
     */
    public function testEarlierBindSurvivesLaterInstall(): void
    {
        $module = new class extends AbstractModule {
            protected function configure(): void
            {
                $this->bind(FakeRobotInterface::class)->to(FakeRobot2::class);
                $this->install(new FakeToBindModule()); // FakeRobotInterface -> FakeRobot
            }
        };

        $instance = $module->getContainer()->getInstance(FakeRobotInterface::class, Name::ANY);

        $this->assertInstanceOf(FakeRobot2::class, $instance);
    }

    /**
     * When two install()ed modules bind the same key, the first install wins,
     * for the same reason install() never overrides: existing entries are kept.
     */
    public function testFirstInstallWinsWhenTwoInstallsCollide(): void
    {
        $module = new class extends AbstractModule {
            protected function configure(): void
            {
                $this->install(new class extends AbstractModule {
                    protected function configure(): void
                    {
                        $this->bind(FakeRobotInterface::class)->to(FakeRobot::class);
                    }
                });
                $this->install(new class extends AbstractModule {
                    protected function configure(): void
                    {
                        $this->bind(FakeRobotInterface::class)->to(FakeRobot2::class);
                    }
                });
            }
        };

        $instance = $module->getContainer()->getInstance(FakeRobotInterface::class, Name::ANY);

        $this->assertInstanceOf(FakeRobot::class, $instance);
    }

    /**
     * In a chain `new A(new B(new C()))` the outermost module wins, and for
     * keys the outermost does not bind, the next-outer module wins. This is
     * the "left context overrides right" rule of BEAR.Sunday contexts.
     */
    public function testOuterModuleWinsAcrossConstructorChain(): void
    {
        $c = new class extends AbstractModule {
            protected function configure(): void
            {
                $this->bind(FakeEngineInterface::class)->to(FakeEngine::class);
                $this->bind(FakeRobotInterface::class)->to(FakeRobot::class);
            }
        };
        $b = new class ($c) extends AbstractModule {
            protected function configure(): void
            {
                $this->bind(FakeEngineInterface::class)->to(FakeEngine2::class);
                $this->bind(FakeRobotInterface::class)->to(FakeRobot2::class);
            }
        };
        $a = new class ($b) extends AbstractModule {
            protected function configure(): void
            {
                $this->bind(FakeEngineInterface::class)->to(FakeEngine3::class);
            }
        };
        $container = $a->getContainer();

        // bound by all three: the outermost A wins
        $this->assertInstanceOf(FakeEngine3::class, $container->getInstance(FakeEngineInterface::class, Name::ANY));
        // not bound by A: the outer of the remaining two (B) wins
        $this->assertInstanceOf(FakeRobot2::class, $container->getInstance(FakeRobotInterface::class, Name::ANY));
    }

    /**
     * override() is the one route that replaces an existing binding: on a
     * colliding key the override module's binding wins over what configure()
     * declared before it.
     */
    public function testOverrideModuleWinsOnCollision(): void
    {
        $module = new class extends AbstractModule {
            protected function configure(): void
            {
                $this->bind(FakeRobotInterface::class)->to(FakeRobot::class);
                $this->override(new class extends AbstractModule {
                    protected function configure(): void
                    {
                        $this->bind(FakeRobotInterface::class)->to(FakeRobot2::class);
                    }
                });
            }
        };

        $instance = $module->getContainer()->getInstance(FakeRobotInterface::class, Name::ANY);

        $this->assertInstanceOf(FakeRobot2::class, $instance);
    }

    /**
     * Re-declaring an untargeted binding to change its scope must override the
     * constructor-chained module's binding. `bind(Concrete::class)->in(SINGLETON)`
     * is the documented idiom to singletonize a concrete class; it must not be
     * silently ignored just because the chained module already bound the class.
     */
    public function testRebindScopeOverridesConstructorChainedBinding(): void
    {
        $chained = new class extends AbstractModule {
            protected function configure(): void
            {
                $this->bind(FakeEngine::class); // untargeted, prototype
            }
        };
        $module = new class ($chained) extends AbstractModule {
            protected function configure(): void
            {
                $this->bind(FakeEngine::class)->in(Scope::SINGLETON);
            }
        };
        $injector = new Injector($module);

        $this->assertSame(
            $injector->getInstance(FakeEngine::class),
            $injector->getInstance(FakeEngine::class)
        );
    }

    /**
     * The module's own interceptor must wrap the constructor-chained module's
     * interceptor when both pointcuts match the same method. With the own
     * FakeDoubleInterceptor outermost, returnSame(2) is (2 + 1) * 2 = 6;
     * if the chained FakeIncrementInterceptor wrapped it instead, the result
     * would be (2 * 2) + 1 = 5.
     */
    public function testOwnInterceptorWrapsConstructorChainedInterceptor(): void
    {
        $chained = new class extends AbstractModule {
            protected function configure(): void
            {
                $this->bindInterceptor(
                    $this->matcher->any(),
                    $this->matcher->any(),
                    [FakeIncrementInterceptor::class]
                );
            }
        };
        $module = new class ($chained) extends AbstractModule {
            protected function configure(): void
            {
                $this->bind(FakeAopInterface::class)->to(FakeAop::class);
                $this->bindInterceptor(
                    $this->matcher->any(),
                    $this->matcher->any(),
                    [FakeDoubleInterceptor::class]
                );
            }
        };
        $instance = (new Injector($module))->getInstance(FakeAopInterface::class);
        assert($instance instanceof FakeAop);

        $this->assertSame(6, $instance->returnSame(2));
    }

    /**
     * Within one configure(), an interceptor declared before install() wraps
     * the installed module's interceptor: pointcuts append in declaration
     * order and earlier means outermost. (2 + 1) * 2 = 6, as above.
     */
    public function testInterceptorDeclaredBeforeInstallWrapsInstalledInterceptor(): void
    {
        $module = new class extends AbstractModule {
            protected function configure(): void
            {
                $this->bind(FakeAopInterface::class)->to(FakeAop::class);
                $this->bindInterceptor(
                    $this->matcher->any(),
                    $this->matcher->any(),
                    [FakeDoubleInterceptor::class]
                );
                $this->install(new class extends AbstractModule {
                    protected function configure(): void
                    {
                        $this->bindInterceptor(
                            $this->matcher->any(),
                            $this->matcher->any(),
                            [FakeIncrementInterceptor::class]
                        );
                    }
                });
            }
        };
        $instance = (new Injector($module))->getInstance(FakeAopInterface::class);
        assert($instance instanceof FakeAop);

        $this->assertSame(6, $instance->returnSame(2));
    }

    /**
     * Multibinding collections keep the same precedence direction: the module's
     * own entries come first in the Map, the constructor-chained module's
     * entries follow.
     */
    public function testOwnMultiBindingEntriesPrecedeConstructorChainedEntries(): void
    {
        $chained = new class extends AbstractModule {
            protected function configure(): void
            {
                $binder = MultiBinder::newInstance($this, FakeEngineInterface::class);
                $binder->addBinding('chained-one')->to(FakeEngine::class);
                $binder->addBinding('chained-two')->to(FakeEngine2::class);
            }
        };
        $module = new class ($chained) extends AbstractModule {
            protected function configure(): void
            {
                $this->bind(FakeMultiBindingConsumer::class);
                $this->bind(FakeEngine::class);
                $this->bind(FakeEngine2::class);
                $this->bind(FakeEngine3::class);
                MultiBinder::newInstance($this, FakeEngineInterface::class)
                    ->addBinding('own')->to(FakeEngine3::class);
                MultiBinder::newInstance($this, FakeRobotInterface::class)
                    ->addBinding('robot')->to(FakeRobot::class);
            }
        };
        $consumer = (new Injector($module))->getInstance(FakeMultiBindingConsumer::class);

        $this->assertSame(
            ['own', 'chained-one', 'chained-two'],
            array_keys(iterator_to_array($consumer->engines))
        );
    }

    /**
     * Every installed sibling contributes its multibindings, even when the
     * installing module declares none of its own.
     *
     * MultiBinder binds MultiBindings::class toInstance() the store of the
     * module it was called on, so each sibling carries its own store into the
     * parent as an ordinary binding while merge() collects the entries into the
     * parent's store. The binding that wins must be the merged store, not
     * whichever sibling happened to be installed first.
     */
    public function testEveryInstalledSiblingContributesMultiBindings(): void
    {
        $module = new class extends AbstractModule {
            protected function configure(): void
            {
                $this->bind(FakeMultiBindingConsumer::class);
                $this->bind(FakeEngine::class);
                $this->bind(FakeEngine2::class);
                $this->bind(FakeRobot::class);
                $this->install(new class extends AbstractModule {
                    protected function configure(): void
                    {
                        MultiBinder::newInstance($this, FakeEngineInterface::class)
                            ->addBinding('first-sibling')->to(FakeEngine::class);
                    }
                });
                $this->install(new class extends AbstractModule {
                    protected function configure(): void
                    {
                        MultiBinder::newInstance($this, FakeEngineInterface::class)
                            ->addBinding('second-sibling')->to(FakeEngine2::class);
                        MultiBinder::newInstance($this, FakeRobotInterface::class)
                            ->addBinding('robot')->to(FakeRobot::class);
                    }
                });
            }
        };
        $consumer = (new Injector($module))->getInstance(FakeMultiBindingConsumer::class);

        $this->assertSame(
            ['first-sibling', 'second-sibling'],
            array_keys(iterator_to_array($consumer->engines))
        );
        $this->assertSame(['robot'], array_keys(iterator_to_array($consumer->robots)));
    }

    /**
     * A same-key multibinding collision across two installs resolves like an
     * ordinary binding collision: the first install's entry wins. Without a
     * decided winner the merge nests both entries into an array and Map
     * iteration dies invoking it as a callable (#345).
     */
    public function testFirstInstallWinsWhenMultiBindingKeysCollideAcrossInstalls(): void
    {
        $module = new class extends AbstractModule {
            protected function configure(): void
            {
                $this->bind(FakeMultiBindingConsumer::class);
                $this->bind(FakeEngine::class);
                $this->bind(FakeEngine2::class);
                $this->bind(FakeEngine3::class);
                $this->install(new class extends AbstractModule {
                    protected function configure(): void
                    {
                        MultiBinder::newInstance($this, FakeEngineInterface::class)
                            ->addBinding('shared-key')->to(FakeEngine::class);
                        MultiBinder::newInstance($this, FakeRobotInterface::class)
                            ->addBinding('robot')->to(FakeRobot::class);
                    }
                });
                $this->install(new class extends AbstractModule {
                    protected function configure(): void
                    {
                        MultiBinder::newInstance($this, FakeEngineInterface::class)
                            ->addBinding('shared-key')->to(FakeEngine2::class);
                        MultiBinder::newInstance($this, FakeEngineInterface::class)
                            ->addBinding('second-only')->to(FakeEngine3::class);
                    }
                });
            }
        };
        $consumer = (new Injector($module))->getInstance(FakeMultiBindingConsumer::class);
        $engines = iterator_to_array($consumer->engines);

        // colliding key: the first install's entry wins
        $this->assertInstanceOf(FakeEngine::class, $engines['shared-key']);
        // non-colliding entries from both installs are kept, in install order
        $this->assertSame(['shared-key', 'second-only'], array_keys($engines));
        $this->assertInstanceOf(FakeEngine3::class, $engines['second-only']);
    }

    /**
     * Unnamed (integer-keyed) entries cannot collide on a key, so a merge
     * appends them from both installs in install order.
     */
    public function testUnnamedMultiBindingEntriesAppendAcrossInstalls(): void
    {
        $module = new class extends AbstractModule {
            protected function configure(): void
            {
                $this->bind(FakeMultiBindingConsumer::class);
                $this->bind(FakeEngine::class);
                $this->bind(FakeEngine2::class);
                $this->install(new class extends AbstractModule {
                    protected function configure(): void
                    {
                        MultiBinder::newInstance($this, FakeEngineInterface::class)
                            ->addBinding()->to(FakeEngine::class);
                        MultiBinder::newInstance($this, FakeRobotInterface::class)
                            ->addBinding('robot')->to(FakeRobot::class);
                    }
                });
                $this->install(new class extends AbstractModule {
                    protected function configure(): void
                    {
                        MultiBinder::newInstance($this, FakeEngineInterface::class)
                            ->addBinding()->to(FakeEngine2::class);
                    }
                });
            }
        };
        $consumer = (new Injector($module))->getInstance(FakeMultiBindingConsumer::class);
        $engines = iterator_to_array($consumer->engines);

        $this->assertSame([0, 1], array_keys($engines));
        $this->assertInstanceOf(FakeEngine::class, $engines[0]);
        $this->assertInstanceOf(FakeEngine2::class, $engines[1]);
    }
}
