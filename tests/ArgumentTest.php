<?php

declare(strict_types=1);

namespace Ray\Di;

use PHPUnit\Framework\TestCase;

class ArgumentTest extends TestCase
{
    /**
     * @var Argument
     */
    protected $argument;

    protected function setUp() : void
    {
        $this->argument = new Argument(new \ReflectionParameter([FakeCar::class, '__construct'], 'engine'), Name::ANY);
    }

    public function testToString()
    {
        $this->assertSame('Ray\Di\FakeEngineInterface-' . Name::ANY, (string) $this->argument);
    }

    public function testToStringScalar()
    {
        $argument = new Argument(new \ReflectionParameter([FakeInternalTypes::class, 'stringId'], 'id'), Name::ANY);
        $this->assertSame('-' . Name::ANY, (string) $argument);
    }

    public function testSerializable()
    {
        /** @var Argument $argument */
        $argument = unserialize(serialize(new Argument(new \ReflectionParameter([FakeInternalTypes::class, 'stringId'], 'id'), Name::ANY)));
        $class = $argument->get()->getDeclaringFunction();
        $this->assertInstanceOf(\ReflectionMethod::class, $class);
    }
}
