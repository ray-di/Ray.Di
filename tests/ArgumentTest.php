<?php

declare(strict_types=1);
/**
 * This file is part of the Ray.Di package.
 *
 * @license http://opensource.org/licenses/MIT MIT
 */
namespace Ray\Di;

use PHPUnit\Framework\TestCase;

class ArgumentTest extends TestCase
{
    /**
     * @var Argument
     */
    protected $argument;

    public function setUp()
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
}
