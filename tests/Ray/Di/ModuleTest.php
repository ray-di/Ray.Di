<?php
namespace Ray\Di;

use Ray\Aop\Bind;

/**
 * Test class for Module.
 */
class ModuleTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Ray\Di\AbstractModule
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
        $this->module->activate();
    }

    protected function tearDown()
    {
        parent::tearDown();
    }

    public function testConfigureTo()
    {
        $expected = [AbstractModule::TO_CLASS, 'Ray\Di\Mock\UserDb'];
        $actual = $this->module['Ray\Di\Mock\DbInterface'][Definition::NAME_UNSPECIFIED][AbstractModule::TO];
        $this->assertSame($expected, $actual);
    }

    public function testConfigureToProvider()
    {
        $module = new Modules\ProviderModule;
        $module->activate();
        $expected = [AbstractModule::TO_PROVIDER, 'Ray\Di\Modules\DbProvider'];
        $actual = $module['Ray\Di\Mock\DbInterface'][Definition::NAME_UNSPECIFIED][AbstractModule::TO];
        $this->assertSame($expected, $actual);
    }

    public function testConfigureToInstance()
    {
        $module = new Modules\InstanceModule;
        $module->activate();
        $expected = [AbstractModule::TO_INSTANCE, new Mock\UserDb];
        $actual = $module['Ray\Di\Mock\DbInterface'][Definition::NAME_UNSPECIFIED][AbstractModule::TO];
        $this->assertSame($expected[0], AbstractModule::TO_INSTANCE);
        $this->assertSame('\Ray\Di\Mock\UserDb', $actual[1]);
    }

    public function testOffsetExists()
    {
        $this->assertTrue(isset($this->module['Ray\Di\Mock\DbInterface']));
    }

    /**
     * @expectedException \Ray\Di\Exception\ReadOnly
     */
    public function testOffsetSet()
    {
        $this->module['Ray\Di\DbInterface'] = 'Ray\Di\Mock\DbInterface';
    }

    /**
     * @expectedException \Ray\Di\Exception\ReadOnly
     */
    public function testOffsetUnset()
    {
        unset($this->module['Ray\Di\Mock\DbInterface']);
    }

    /**
     * @covers Ray\Di\AbstractModule::__toString
     */
    public function testToString()
    {
        $this->assertTrue(is_string((string)$this->module));
    }

    /**
     * @expectedException \Ray\Di\Exception\InvalidProvider
     */
    public function testToProviderInvalid()
    {
        (new Modules\InvalidProviderModule)->activate();
    }

    public function testToStringInstance()
    {
        $module = new Modules\InstanceModule;
        $module->activate();
        $this->assertInternalType('string', (string)$module);
    }

    public function testToStringInstance2()
    {
        $module = new Modules\InstanceModule2;
        $module->activate();
        $this->assertInternalType('string', (string)$module);
    }


    public function testToStringInstanceArray()
    {
        $module = new Modules\ArrayInstance;
        $this->assertInternalType('string', (string)$module);
    }

    public function testToStringDecoratedModule()
    {
        $module = new Modules\BasicModule(new Modules\ArrayInstance);
        $module->activate();
        $this->assertInternalType('string', (string)$module);
    }

    /**
     * This module binds nothing
     */
    public function testInvokeReturnFalse()
    {
        $module = $this->module;
        $module->activate();
        /** @var $module callable */
        $binder = $module('Ray\Di\Aop\RealBillingService', new Bind);
        /** @var $binder Bind */
        $this->assertSame(false, $binder->hasBinding());
    }

    public function testInvokeReturnBinder()
    {
        $module = new Modules\AopMatcherModule;
        $binder = $module('Ray\Di\Aop\RealBillingService', new Bind);
        $this->assertInstanceOf('\Ray\Aop\Bind', $binder);
    }

    public function testAopAnyMatcherModule()
    {
        $module = new Modules\AopAnyMatcherModule;
        $module->activate();
        $bind = $module('Ray\Di\Aop\RealBillingService', new Bind);
        $this->assertInstanceOf('Ray\Aop\Bind', $bind);
        $interceptors = $bind('chargeOrderWithNoTax');
        $this->assertInstanceOf('\Ray\Di\Aop\TaxCharger', $interceptors[0]);
    }

    public function testAopAnnotateMatcherModule()
    {
        $module = new Modules\AopAnnotateMatcherModule;
        $module->activate();
        $bind = $module('Ray\Di\Aop\RealBillingService', new Bind);
        $result = $bind('chargeOrderWithNoTax');
        $this->assertSame(false, $result);
    }

    public function testAopAnnotateMatcherModuleGetCorrectInterceptor()
    {
        $module = new Modules\AopAnnotateMatcherModule;
        $module->activate();
        $bind = $module('Ray\Di\Aop\RealBillingService', new Bind);
        $result = $bind('chargeOrder');
        $this->assertInstanceOf('\Ray\Di\Aop\TaxCharger', $result[0]);
    }

    public function testInstall()
    {
        $module = new Modules\InstallModule;
        $module->activate();
        $this->assertTrue(isset($module->bindings['Ray\\Di\\Mock\\DbInterface']));
        $this->assertTrue(isset($module->bindings['Ray\\Di\\Mock\\LogInterface']));
    }

    public function testInstallPointcuts()
    {
        $module = new Modules\InstallPointcutsModule;
        $module->activate();
        $this->assertSame(2, count($module->pointcuts));
    }

    public function testSerializeModule()
    {
        $module = new Modules\AopAnnotateMatcherModule;
        $module->activate();
        $wakedModule = unserialize(serialize($module));
        $this->assertObjectHasAttribute('pointcuts', $wakedModule);
        $this->assertTrue($wakedModule->pointcuts instanceof \ArrayObject);
    }

    public function test_installModuleCount()
    {
        $module = new Modules\TimeModule;
        $this->module->install($module);
        $this->assertSame(2, count((array)($this->module->bindings)));
    }

    public function test_mergeModuleContent()
    {
        $module = new Modules\TimeModule;
        $this->module->install($module);
        $bindings = $this->module->bindings;
        $bindingClass = array_keys((array)$bindings);
        $this->assertSame($bindingClass, ["Ray\\Di\\Mock\\DbInterface", '']);
    }

    public function test_requestInjection()
    {
        $module = new Modules\RequestInjectionModule;
        $module->activate();
        $this->assertInstanceOf('Ray\Di\Definition\Basic', $module->object);
        $this->assertInstanceOf('Ray\Di\Mock\UserDb', $module->object->db);
    }

    /**
     * count($result) is 4, but latter 2 is ignored.
     * only first bind is valid.
     */
    public function test_installModuleTwice()
    {
        $module = new Modules\TwiceInstallModule;
        $module->activate();
        $bindings = (array)$module->bindings;
        $result = $bindings['']['val_a']['to'];
        $this->assertSame('instance', $result[0]);
        $this->assertSame(1, $result[1]);
    }

    /**
     * bind 1;
     * bind 2;
     *
     * -> bind2
     */
    public function test_doubleBindWithInstall()
    {
        $module = new Modules\DoubleBindModule;
        $module->activate();
        $bindings = (array)$module->bindings;
        $result = $bindings['Ray\\Di\\Mock\\DbInterface']['*']['to'][1];
        $this->assertSame($result, 'Ray\Di\Mock\UserDb2');
    }

    /**
     * bind 1;
     * bind 2;
     *
     * -> bind2
     */
    public function test_doubleInstall1()
    {
        $module = new Modules\InstallDoubleModule1;
        $module->activate();
        $bindings = (array)$module->bindings;
        $result = $bindings['Ray\\Di\\Mock\\DbInterface']['*']['to'][1];
        $this->assertSame($result, 'Ray\Di\Mock\UserDb1');
    }

    public function test_doubleInstall2()
    {
        $module = new Modules\InstallDoubleModule2;
        $module->activate();
        $bindings = (array)$module->bindings;
        $result = $bindings['Ray\\Di\\Mock\\DbInterface']['*']['to'][1];
        $this->assertSame($result, 'Ray\Di\Mock\UserDb2');
    }

}
