<?php

declare(strict_types=1);

namespace Ray\Di;

use PHPUnit\Framework\TestCase;

class NameTest extends TestCase
{
    public function testUnName()
    {
        $name = new Name(Name::ANY);
        $parameter = new \ReflectionParameter([FakeCar::class, '__construct'], 'engine');
        $boundName = $name($parameter);
        $this->assertSame(Name::ANY, $boundName);
    }

    public function testSingleName()
    {
        $name = new Name('turbo');
        $parameter = new \ReflectionParameter([FakeCar::class, '__construct'], 'engine');
        $boundName = $name($parameter);
        $this->assertSame('turbo', $boundName);
    }

    /**
     * @dataProvider keyPairStringProvider
     */
    public function testKeyValuePairName(string $keyPairValueString)
    {
        $name = new Name($keyPairValueString);
        $parameter = new \ReflectionParameter([FakeCar::class, '__construct'], 'engine');
        $boundName = $name($parameter);
        $this->assertSame('engine_name', $boundName);
    }

    public function keyPairStringProvider()
    {
        return [
            ['engine=engine_name,var=va_name'],
            ['engine=engine_name, var=va_name'],
            ['var=var_name,engine=engine_name'],
            ['var=var_name, engine=engine_name']
        ];
    }

    public function testKeyValuePairButNotFound()
    {
        $name = new Name('foo=bar');
        $parameter = new \ReflectionParameter([FakeCar::class, '__construct'], 'engine');
        $boundName = $name($parameter);
        $this->assertSame(Name::ANY, $boundName);
    }

    public function testSetName()
    {
        $name = new Name(FakeMirrorRight::class);
        $parameter = new \ReflectionParameter([FakeHandleBar::class, 'setMirrors'], 'rightMirror');
        $boundName = $name($parameter);
        $expected = FakeMirrorRight::class;
        $this->assertSame($expected, $boundName);
    }
}
