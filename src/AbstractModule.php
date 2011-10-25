<?php
/**
 * Ray
 *
 * @license  http://opensource.org/licenses/bsd-license.php BSD
 */
namespace Ray\Di;

use Ray\Di\Exception,
    Ray\Aop\Bind;

class jointPointMatch {
    public $classMatcher;
    public $methodMatcher;
    public $interceptors;
}

use Ray\Di\Exception\ReadOnly;

/**
 * A module contributes configuration information, typically interface bindings, which will be used to create an Injector.
 *
 * @package Ray.Di
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

    public $annotations = array();

    public $pointcuts = array();


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
        if (class_exists($class) === false) {
            throw new Exception\InvalidToBinding($class);
        }
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
        $hasProviderInterface = class_exists($provider) && in_array('Ray\Di\ProviderInterface', class_implements($provider));
        if ($hasProviderInterface === false) {
            throw new Exception\InvalidProviderBinding($provider);
        }
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

    protected function registerInterceptAnnotation($annotation, array $interceptors)
    {
        $this->annotations[$annotation] = $interceptors;
    }

    protected function bindInterceptor(\Closure $classMatcher, \Closure $methodMatcher, array $interceptors)
    {
        $this->pointcuts[] = array($classMatcher, $methodMatcher, $interceptors);
    }

    public function __invoke($class)
    {
        foreach ($this->pointcuts as $pointcut) {
            list($classMatcher, $methodMatcher, $interceptors) = $pointcut;
            if ($classMatcher($class) === true) {
                $bind = new Bind;
                $bind->bindMatcher($methodMatcher, $interceptors);
                return $bind;
            }
        }
        return false;
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
        return 'bindings->' . json_encode($this->bindings) . ',annotation->' . json_encode($this->annotations);
    }
}
