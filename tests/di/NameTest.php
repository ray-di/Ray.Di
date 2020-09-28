<?php

declare(strict_types=1);

namespace Ray\Di;

use PHPUnit\Framework\TestCase;
use ReflectionParameter;

class NameTest extends TestCase
{
    public function testUnName() : void
    {
        $name = new Name(Name::ANY);
        $parameter = new ReflectionParameter([FakeCar::class, '__construct'], 'engine');
        $boundName = $name($parameter);
        $this->assertSame(Name::ANY, $boundName);
    }

    public function testSingleName() : void
    {
        $name = new Name('turbo');
        $parameter = new ReflectionParameter([FakeCar::class, '__construct'], 'engine');
        $boundName = $name($parameter);
        $this->assertSame('turbo', $boundName);
    }

    /**
     * @dataProvider keyPairStringProvider
     */
    public function testKeyValuePairName(string $keyPairValueString) : void
    {
        $name = new Name($keyPairValueString);
        $parameter = new ReflectionParameter([FakeCar::class, '__construct'], 'engine');
        $boundName = $name($parameter);
        $this->assertSame('engine_name', $boundName);
    }

    /**
     * @return string[][]
     *
     * @psalm-return array{0: array{0: string}, 1: array{0: string}, 2: array{0: string}, 3: array{0: string}}
     */
    public function keyPairStringProvider() : array
    {
        return [
            ['engine=engine_name,var=va_name'],
            ['engine=engine_name, var=va_name'],
            ['var=var_name,engine=engine_name'],
            ['var=var_name, engine=engine_name']
        ];
    }

    public function testKeyValuePairButNotFound() : void
    {
        $name = new Name('foo=bar');
        $parameter = new ReflectionParameter([FakeCar::class, '__construct'], 'engine');
        $boundName = $name($parameter);
        $this->assertSame(Name::ANY, $boundName);
    }

    public function testSetName() : void
    {
        $name = new Name(FakeMirrorRight::class);
        $parameter = new ReflectionParameter([FakeHandleBar::class, 'setMirrors'], 'rightMirror');
        $boundName = $name($parameter);
        $expected = FakeMirrorRight::class;
        $this->assertSame($expected, $boundName);
    }
}
