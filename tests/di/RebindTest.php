<?php

declare(strict_types=1);

namespace Ray\Di;

use PHPUnit\Framework\TestCase;
use Ray\Di\Exception\Unbound;

class RebindTest extends TestCase
{
    public function testRebind(): void
    {
        $module = new class extends AbstractModule{
            protected function configure()
            {
                $this->bind(FakeFooInterface::class)->to(FakeFoo::class);
                $this->rebind(FakeFooInterface::class, 'new');
            }
        };
        $foo = (new Injector($module))->getInstance(FakeFooInterface::class, 'new');
        $this->assertInstanceOf(FakeFoo::class, $foo);
    }

    public function testRebindInOtherModule(): void
    {
        $module1 = new class extends AbstractModule{
            protected function configure()
            {
                $this->bind(FakeFooInterface::class)->to(FakeFoo::class);
            }
        };
        $module2 = new class ($module1) extends AbstractModule{
            public function __construct(?AbstractModule $module = null)
            {
                parent::__construct($module);
            }

            protected function configure()
            {
                $this->rebind(FakeFooInterface::class, 'new');
            }
        };
        $module1->install($module2);
        $foo = (new Injector($module1))->getInstance(FakeFooInterface::class, 'new');
        $this->assertInstanceOf(FakeFoo::class, $foo);
    }

    public function testRebindInOtherModuleNotPossible(): void
    {
        $this->expectException(Unbound::class);
        $module1 = new class extends AbstractModule{
            protected function configure()
            {
                $this->bind(FakeFooInterface::class)->to(FakeFoo::class);
            }
        };
        $module2 = new class extends AbstractModule{
            protected function configure()
            {
                $this->rebind(FakeFooInterface::class, 'new'); // no binding at this module
            }
        };
        $module1->install($module2);
        (new Injector($module1))->getInstance(FakeFooInterface::class, 'new');
    }
}
