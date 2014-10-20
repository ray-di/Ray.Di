<?php

namespace Ray\Di;

class ParametersTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var Parameters
     */
    protected $parameters;

    public function setUp()
    {
        $this->parameters = new Parameters(new \ReflectionMethod(FakeCar::class, 'setTires'), new Name(Name::ANY));
    }

    public function testInject()
    {
        $container = (new FakeCarModule)->getContainer();
        $parameters = $this->parameters->get($container);
        $this->assertInstanceOf(FakeTyre::class, $parameters[0]);
        $this->assertInstanceOf(FakeTyre::class, $parameters[1]);
        $this->assertNotSame(spl_object_hash($parameters[0]), $parameters[1]);
    }

    public function testParameterDefaultValue()
    {
        $defaultValue = (new \ReflectionParameter([FakeHandleProvider::class, '__construct'], 'logo'))->getDefaultValue();
        $emptyContainer = new Container;
        $parameters = new Parameters(new \ReflectionMethod(FakeHandleProvider::class, '__construct'), new Name(Name::ANY));
        $parametersValue = $parameters->get($emptyContainer);
        $this->assertSame($defaultValue, $parametersValue[0]);
    }
}
