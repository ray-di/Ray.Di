<?php

namespace Ray\Di;

use Ray\Di\Exception\Unnamed;

class ArgumentsTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var Arguments
     */
    protected $arguments;

    public function setUp()
    {
        $this->arguments = new Arguments(new \ReflectionMethod(FakeCar::class, 'setTires'), new Name(Name::ANY));
    }

    public function testInject()
    {
        $container = (new FakeCarModule)->getContainer();
        $parameters = $this->arguments->get($container);
        $this->assertInstanceOf(FakeTyre::class, $parameters[0]);
        $this->assertInstanceOf(FakeTyre::class, $parameters[1]);
        $this->assertNotSame(spl_object_hash($parameters[0]), $parameters[1]);
    }

    public function testParameterDefaultValue()
    {
        $defaultValue = (new \ReflectionParameter([FakeHandleProvider::class, '__construct'], 'logo'))->getDefaultValue();
        $emptyContainer = new Container;
        $parameters = new Arguments(new \ReflectionMethod(FakeHandleProvider::class, '__construct'), new Name(Name::ANY));
        $parametersValue = $parameters->get($emptyContainer);
        $this->assertSame($defaultValue, $parametersValue[0]);
    }

    public function testUnnamedArgument()
    {
        $this->setExpectedException(Unnamed::class);
        new Argument(new \ReflectionParameter([FakeNoName::class, '__construct'], 'noTypehintNoName'), Name::ANY);
    }
}
