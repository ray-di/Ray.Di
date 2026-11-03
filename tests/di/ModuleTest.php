<?php

declare(strict_types=1);

namespace Ray\Di;

use PHPUnit\Framework\TestCase;
use Ray\Aop\Matcher;
use Ray\Di\Exception\NotFound;

use function str_replace;

class ModuleTest extends TestCase
{
    public function testConstructionRegistersBindings(): void
    {
        $module = new FakeInstanceBindModule();
        $this->assertSame(1, $module->getContainer()->getInstance('', 'one'));
    }

    public function testInstall(): void
    {
        $module = new FakeInstallModule();
        // both installed modules' bindings are resolvable with their bound values
        $this->assertSame(1, $module->getContainer()->getInstance('', 'one'));
        $this->assertSame(2, $module->getContainer()->getInstance('', 'two'));
    }

    public function testToInvalidClass(): void
    {
        $this->expectException(NotFound::class);
        new FakeToBindInvalidClassModule();
    }

    public function testRename(): void
    {
        $module = new FakeRenameModule(new FakeToBindModule());
        $instance = $module->getContainer()->getInstance(FakeRobotInterface::class, 'original');
        // assert the concrete class: the renamed binding must be the one the
        // chained module registered, not merely something type-compatible
        $this->assertInstanceOf(FakeRobot::class, $instance);
    }

    public function testModuleWithoutParentConstructorCall(): void
    {
        $module = new FakeNoConstructorCallModule();
        // configure() never ran in the constructor; getContainer() must
        // activate the module lazily so its bindings resolve
        $instance = $module->getContainer()->getInstance(FakeRobotInterface::class, Name::ANY);
        $this->assertInstanceOf(FakeRobot::class, $instance);
    }

    public function testToString(): void
    {
        $string = (string) new FakeLogStringModule();
        $normalize = static function (string $str): string {
            return str_replace(["\r\n", "\r"], "\n", $str);
        };
        $this->assertSame($normalize('-array => (array)
-bool => (boolean)
-int => (integer)
-null => (NULL)
-object => (object) stdClass
-string => (string)
Ray\Di\FakeAopInterface- => (dependency) Ray\Di\FakeAop (aop) +returnSame(Ray\Di\FakeDoubleInterceptor)
Ray\Di\FakeDoubleInterceptor- => (untargeted)
Ray\Di\FakeRobotInterface- => (provider) (dependency) Ray\Di\FakeRobotProvider'), $normalize($string));
    }

    public function testToStringWithoutPointcuts(): void
    {
        $module = new class extends AbstractModule {
            protected function configure(): void
            {
                $this->bind(FakeEngine::class);
            }
        };

        $this->assertSame(FakeEngine::class . '- => (untargeted)', (string) $module);
    }

    /** Identical target classes share one AOP preview without mutating either dependency. */
    public function testToStringCachesAspectPreview(): void
    {
        $module = new class extends AbstractModule {
            protected function configure(): void
            {
                $this->bind(FakeAopInterface::class)->annotatedWith('one')->to(FakeAop::class);
                $this->bind(FakeAopInterface::class)->annotatedWith('two')->to(FakeAop::class);
            }
        };
        $classMatcher = new FakeCountingMatcher();
        $module->bindInterceptor($classMatcher, (new Matcher())->any(), [FakeDoubleInterceptor::class]);
        $container = $module->getContainer()->getContainer();
        $first = $container[FakeAopInterface::class . '-one'];
        $second = $container[FakeAopInterface::class . '-two'];

        $moduleString = (string) $module;

        $this->assertSame(1, $classMatcher->matches);
        $this->assertSame('(dependency) ' . FakeAop::class, (string) $first);
        $this->assertSame('(dependency) ' . FakeAop::class, (string) $second);
        $this->assertStringContainsString(
            FakeAopInterface::class . '-one => (dependency) ' . FakeAop::class . ' (aop)',
            $moduleString,
        );
        $this->assertStringContainsString(
            FakeAopInterface::class . '-two => (dependency) ' . FakeAop::class . ' (aop)',
            $moduleString,
        );
    }
}
