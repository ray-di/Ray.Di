<?php
/**
 * File doc
 *
 * @package Package.name
 * @license licence name
 */
namespace Vendor\Package;

use FooClass;
use BarClass as Bar;
use OtherVendor\OtherPackage\BazClass;

/**
 * Class doc
 *
 * @package    BEAR.app
 * @author     $Author:$ <username@example.com>
 */
class ClassName extends ParentClass implements
    InterfaceName,
    AnotherInterfaceName,
    YetAnotherInterface,
    InterfaceInterface
{
    const CONSTANT_NAME = 'constant value';

    /**
     * @var array
     */
    protected static $foo;

    /**
     * @return void
     */
    abstract protected zim();

    /**
     * @return void
     */
    final public static bar()
    {
        // method body
    }

    /**
     * @var array
     */
    public $foo = null;
    // constants, properties, methods

    /**
     * Method
     *
     * @param string  $arg1
     * @param boolean &$arg2 optional comment
     * @param integer $arg3
     *
     * @return void
     */
    public function fooBarBaz($arg1, &$arg2, $arg3 = [])
    {
        // method body
    }

    /**
     * aVeryLongMethodName
     *
     * @return void
     */
    public function aVeryLongMethodName(
        ClassTypeHint $arg1,
        &$arg2,
        array $arg3 = []
    ) {
        // method body
    }


    /**
     * Control Structures
     *
     * @return void
     */
    public function controlStructures()
    {
        if ($expr1) {
            // if body
        } elseif ($expr2) {
            // elseif body
        } else {
            // else body;
        }

        switch ($expr) {
            case 1:
                echo 'First case';
                break;
            case 2:
                echo 'Second case';
                // no break
            default:
                echo 'Default case';
                break;
        }

        while ($expr) {
            // structure body
        }

        do {
            // structure body;
        } while ($expr);

        for ($i = 0; $i < 10; $i++) {
            // for body
        }

        foreach ($iterable as $key => $value) {
            // foreach body
        }

        try {
            // try body
        } catch (FirstExceptionType $e) {
            // catch body
        } catch (OtherExceptionType $e) {
            // catch body
        }

        $multiLineArray = array(
            'hello' => 'world',
            'abc' => 'def'
        );
        multiline_function_call(
            1,
            'abc',
        );
    }
}