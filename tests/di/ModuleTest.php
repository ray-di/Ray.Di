<?php

declare(strict_types=1);

namespace Ray\Di;

use PHPUnit\Framework\TestCase;
use Ray\Di\Exception\NotFound;

use function str_replace;

class ModuleTest extends TestCase
{
    public function testNew(): void
    {
        $module = new FakeInstanceBindModule();
        $this->assertInstanceOf(AbstractModule::class, $module);
    }

    public function testInstall(): void
    {
        $module = new FakeInstallModule();
        $this->assertInstanceOf(AbstractModule::class, $module);
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
        $this->assertInstanceOf(FakeRobotInterface::class, $instance);
    }

    public function testConstructorCallModule(): void
    {
        $module = new FakelNoConstructorCallModule();
        $container = $module->getContainer();
        $this->assertInstanceOf(Container::class, $container);
    }

    public function testActivate(): void
    {
        $module = new FakeInstanceBindModule();
        $this->assertInstanceOf(Container::class, $module->getContainer());
    }

    public function testtoString(): void
    {
        $string = (string) new FakeLogStringModule();
        $normalize = static function (string $str): string {
            return str_replace(["\r\n", "\r"], "\n", $str);
        };
        $expected = <<<'EOT'
module
├── ''
│   ├── named:array ─ toInstance:(array)
│   ├── named:bool ─ toInstance:true
│   ├── named:int ─ toInstance:1
│   ├── named:null ─ toInstance:null
│   ├── named:object ─ toInstance:(stdClass)
│   └── named:string ─ toInstance:'1'
├── Ray\Di\FakeAopInterface
│   └── to:Ray\Di\FakeAop
│       └─intercept─ returnSame: Ray\Di\FakeDoubleInterceptor
├── Ray\Di\FakeDoubleInterceptor
│   └── to:Ray\Di\FakeDoubleInterceptor ─ in:Singleton
└── Ray\Di\FakeRobotInterface
    └── toProvider:Ray\Di\FakeRobotProvider ─ in:Singleton
EOT;
        $this->assertSame($normalize($expected), $normalize($string));
    }

    public function testtoStringWithToNull(): void
    {
        $module = new class extends AbstractModule {
            protected function configure(): void
            {
                $this->bind(FakeRobotInterface::class)->toNull();
            }
        };
        $string = (string) $module;
        $this->assertStringContainsString('toNull', $string);
    }
}
