<?php
/**
 *
 * This file is part of the Aura Project for PHP.
 *
 * @license http://opensource.org/licenses/bsd-license.php BSD
 *
 */
namespace Aura\Di;

use Aura\Di\Exception\ReadOnly;

/**
 * A module contributes configuration information, typically interface bindings, which will be used to create an Injector.
 *
 * @package Aura.Di
 * @author  Akihito Koriyama <akihito.koriyama@gmail.com>
 */
abstract class AbstractModule implements \ArrayAccess
{
    const BIND = 'bind';

    const NAME = 'name';

    const IN = 'in';

    const TO = 'to';

    const TO_CLASS = 'class';

    const TO_PROVIDER = 'provider';

    const TO_INSTANCE = 'instance';

    const TO_CLOSURE = 'closure';

    const SCOPE = 'scope';

    const NAME_UNSPECIFIED = '*';

    /**
     * @var Definition
     */
    protected $bindings;

    /**
     * @var array
     */
    protected $params = array();

    /**
     * @var string
     */
    protected $currentBinding;

    /**
     * @var string
     */
    protected $currentName = self::NAME_UNSPECIFIED;

    /**
     * Scope
     *
     * @var array
     */
    protected $scope = array(Scope::PROTOTYPE, Scope::SINGLETON);

    /**
     * Constructor
     */
    public function __construct(AbstractModule $module = null)
    {
        $this->bindings = is_null($module) ? new \ArrayObject : $module;
        $this->configure();
    }

    /**
     * Configures a Binder via the exposed methods.
     *
     * @return void
     */
    abstract protected function configure();

    /**
     * Set bind interface
     *
     * @param string $interface
     *
     * @return AbstractModule
     */
    protected function bind($interface = '')
    {
        $this->currentBinding = $interface;
        $this->currentName = self::NAME_UNSPECIFIED;
        return $this;
    }

    /**
     * Set binding annotattion.
     *
     * @param string $name
     *
     * @return AbstractModule
     */
    protected function annotatedWith($name)
    {
        $this->currentName = $name;
        $this->bindings[$this->currentBinding][$name] = array(self::IN => Scope::SINGLETON);
        return $this;
    }

    /**
     * Set scope
     *
     * @param string $name
     *
     * @return AbstractModule
     */
    protected function in($scope)
    {
        $this->bindings[$this->currentBinding][$this->currentName][self::IN] = $scope;
        return $this;
    }

    /**
     * To class
     *
     * @param string $class
     *
     * @return AbstractModule
     */
    protected function to($class)
    {
        $this->bindings[$this->currentBinding][$this->currentName] = array(
            self::TO => array(self::TO_CLASS, $class)
        );
        return $this;
    }

    /**
     * To provider
     *
     * @param string $provider provider class
     *
     * @return AbstractModule
     */
    protected function toProvider($provider)
    {
        $this->bindings[$this->currentBinding][$this->currentName] = array(
            self::TO => array(self::TO_PROVIDER, $provider)
        );
    }

    /**
     * To instance
     *
     * @param object $instance
     *
     * @return AbstractModule
     */
    protected function toInstance($instance)
    {
        $this->bindings[$this->currentBinding][$this->currentName] = array(
            self::TO => array(self::TO_INSTANCE, $instance)
        );
    }

    /**
     * To closure
     *
     * @param Closure $closure
     */
    protected function toClosure(\Closure $closure)
    {
        $this->bindings[$this->currentBinding][$this->currentName] = array(
            self::TO => array(self::TO_CLOSURE, $closure)
        );
    }

    /**
     * @param offset
     */
    public function offsetExists ($offset) {
        return isset($this->bindings[$offset]);
    }

    /**
     * @param offset
     */
    public function offsetGet ($offset) {
        return isset($this->bindings[$offset]) ? $this->bindings[$offset] : null;
    }

    /**
     * @param offset
     * @param value
     */
    public function offsetSet ($offset, $value) {
        throw new Exception\ReadOnly;
    }

    /**
     * @param offset
     */
    public function offsetUnset ($offset) {
        throw new Exception\ReadOnly;
    }

    /**
     * @return string
     */
    public function __toString()
    {
        return var_export($this->bindings, true);
    }
}