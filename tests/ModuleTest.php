<?php
namespace Aura\Di;

/**
 * Test class for Module.
 */
class ModuleTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var Forge
     */
    protected $module;

    protected $config;

    const NAME = 'user_db';

    /**
     * Sets up the fixture, for example, opens a network connection.
     * This method is called before a test is executed.
     */
    protected function setUp()
    {
        parent::setUp();
        $this->module = new Modules\BasicModule();
    }

    /**
     * Tears down the fixture, for example, closes a network connection.
     * This method is called after a test is executed.
     */
    protected function tearDown()
    {
        parent::tearDown();
    }

    public function atestConfigureBind()
    {
        $expected = 'Aura\Di\Mock\DbInterface';
        $actual = $this->module[AbstractModule::BIND];
        $this->assertSame($expected, $actual);
    }

    public function testConfigureTo()
    {
        $expected = array(AbstractModule::TO_CLASS, 'Aura\Di\Mock\UserDb');
        $actual = $this->module['Aura\Di\Mock\DbInterface'][Definition::NAME_UNSPECIFIED][AbstractModule::TO];
        $this->assertSame($expected, $actual);
    }

//     public function testConfigureIn()
//     {
//         $expected = Scope::SINGLETON;
//         $actual = $this->module['Aura\Di\DbInterface'][Definition::NAME_UNSPECIFIED][AbstractModule::IN];
//         $this->assertSame($expected, $actual);
//     }

    public function testConfigureToProvider()
    {
        $module = new Modules\ProviderModule;
        $expected = array(AbstractModule::TO_PROVIDER, 'Aura\Di\Modules\DbProvider');
        $actual = $module['Aura\Di\Mock\DbInterface'][Definition::NAME_UNSPECIFIED][AbstractModule::TO];
        $this->assertSame($expected, $actual);
    }

    public function testConfigureToInstance()
    {
        $module = new Modules\InstanceModule;
        $expected = array(AbstractModule::TO_INSTANCE, new Mock\UserDb());
        $actual = $module['Aura\Di\Mock\DbInterface'][Definition::NAME_UNSPECIFIED][AbstractModule::TO];
        $this->assertSame($expected[0], AbstractModule::TO_INSTANCE);
        $this->assertSame('\Aura\Di\Mock\UserDb', $actual[1]);
    }

    public function testOffsetExists()
    {
        $this->assertTrue(isset($this->module['Aura\Di\Mock\DbInterface']));
    }

    /**
     * @expectedException Aura\Di\Exception\ReadOnly
     */
    public function testOffsetSet()
    {
        $this->module['Aura\Di\DbInterface'] = 'Aura\Di\Mock\DbInterface';
    }

    /**
     * @expectedException Aura\Di\Exception\ReadOnly
     */
    public function testOffsetUnset()
    {
        unset($this->module['Aura\Di\Mock\DbInterface']);
    }

    /**
     * @covers Aura\Di\AbstractModule::__toString
     */
    public function testToString()
    {
        $this->assertTrue(is_string((string)$this->module));
    }
}