<?php

declare(strict_types=1);

namespace Ray\Di;

use PHPUnit\Framework\TestCase;
use Ray\Di\Exception\NoHint;
use Ray\Di\Exception\Unbound;

class NoHintTest extends TestCase
{
    public function testNoHintIsUnbound(): void
    {
        $e = new NoHint();
        $this->assertInstanceOf(Unbound::class, $e);
    }

    public function testNoHintThrownForNoTypeNoName(): void
    {
        $this->expectException(NoHint::class);

        $injector = new Injector(new FakeUnNamedModule());
        $injector->getInstance(FakeUnNamedClass::class);
    }

    public function testNoHintMessageFormat(): void
    {
        $injector = new Injector(new FakeUnNamedModule());

        try {
            $injector->getInstance(FakeUnNamedClass::class);
            $this->fail('NoHint exception should be thrown');
        } catch (NoHint $e) {
            // Message format: ${var} (file:line)
            $this->assertMatchesRegularExpression(
                '/^\$\w+ \(.+:\d+\)$/',
                $e->getMessage(),
            );
            $this->assertStringContainsString('$value', $e->getMessage());
            $this->assertStringContainsString('FakeUnNamedClass.php', $e->getMessage());
        }
    }
}
