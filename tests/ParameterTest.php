<?php

namespace Ray\Di;

class ParameterTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var Parameter
     */
    protected $parameter;

    public function setUp()
    {
        $this->parameter = new Parameter(new \ReflectionParameter([FakeCar::class, '__construct'], 'engine'), Name::ANY);
    }

    public function testToString()
    {
        $this->assertSame('Ray\Di\FakeEngineInterface-*', (string) $this->parameter);
    }
}
