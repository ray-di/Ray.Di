<?php

namespace Ray\Di;

/**
 * Test class for Annotation.
 */
class DocSampleZf2Test extends \PHPUnit_Framework_TestCase
{
    private $systemRoot;

    protected function setUp()
    {
        parent::setUp();
        $this->systemRoot = dirname(__DIR__);
    }

    public function test_01()
    {
        $cli = 'php ' . $this->systemRoot . '/doc/zf2-di-tests-clone/01-runtime-constructor-injection.php';
        passthru($cli, $return);
        $this->expectOutputString('It works!');
    }

    public function test_01A()
    {
        $cli = 'php ' . $this->systemRoot . '/doc/zf2-di-tests-clone/01A-runtime-constructor-injection-lazy.php';
        passthru($cli, $return);
        $this->expectOutputString('It works!');
    }

    public function test_03()
    {
        $cli = 'php ' . $this->systemRoot . '/doc/zf2-di-tests-clone/03-runtime-setter-injection.php';
        passthru($cli, $return);
        $this->expectOutputString('It works!');
    }

    public function test_04()
    {
        $cli = 'php ' . $this->systemRoot . '/doc/zf2-di-tests-clone/04-constructor-injection-with-config-params.php';
        passthru($cli, $return);
        $this->expectOutputString('It works!');
    }

    public function test_05()
    {
        $cli = 'php ' . $this->systemRoot . '/doc/zf2-di-tests-clone/05-constructor-injection-with-calltime-params.php';
        passthru($cli, $return);
        $this->expectOutputString('It works!');
    }

    public function test_09()
    {
        $cli = 'php ' . $this->systemRoot . '/doc/zf2-di-tests-clone/09-runtime-setter-injection-with-annotation.php';
        passthru($cli, $return);
        $this->expectOutputString('It works!');
    }

    public function test_09A()
    {
        $cli = 'php ' . $this->systemRoot . '/doc/zf2-di-tests-clone/09A-runtime-setter-injection-with-annotation-interface.php';
        passthru($cli, $return);
        $this->expectOutputString('It works!');
    }

    public function test_12()
    {
        $cli = 'php ' . $this->systemRoot . '/doc/zf2-di-tests-clone/12-setter-injection-with-annotation.php';
        passthru($cli, $return);
        $this->expectOutputString('It works!');
    }

    public function test_12A()
    {
        $cli = 'php ' . $this->systemRoot . '/doc/zf2-di-tests-clone/12A-setter-injection-with-annotation-interface.php';
        passthru($cli, $return);
        $this->expectOutputString('It works!');
    }
}
