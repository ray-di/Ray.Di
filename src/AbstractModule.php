<?php
/**
 * Ray
 *
 * @package Ray.Di
 * @license http://opensource.org/licenses/bsd-license.php BSD
 */
namespace Ray\Di;

use Ray\Di\Exception,
    Ray\Di\Exception\ReadOnly,
    Ray\Aop\Bind;

/**
 * A module contributes configuration information, typically interface bindings,
 *  which will be used to create an Injector.
 *
 * @package Ray.Di
 * @author  Akihito Koriyama <akihito.koriyama@gmail.com>
 */
abstract class AbstractModule implements \ArrayAccess
{
    /**
     * Bind
     *
     * @var string
     */
    const BIND = 'bind';

    /**
     * Name
     *
     * @var string
     */
    const NAME = 'name';

    /**
     * In (Scope)
     *
     * @var string
     */
    const IN = 'in';

    /**
     * To
     *
     * @var string
     */
    const TO = 'to';

    /**
     * To Class
     *
     * @var string
     */
    const TO_CLASS = 'class';

    /**
     * Provider
     *
     * @var string
     */
    const TO_PROVIDER = 'provider';

    /**
     * To Instance
     *
     * @var string
     */
    const TO_INSTANCE = 'instance';

    /**
     * To Closure
     *
     * @var string
     */
    const TO_CLOSURE = 'closure';

    /**
     * To Scope
     *
     * @var string
     */
    const SCOPE = 'scope';

    /**
     * Unspecified name
     *
     * @var string
     */
    const NAME_UNSPECIFIED = '*';

    /**
     * Annotation
     *
     * @var \ArrayObject
     */
    public $annotations = array();

    /**
     * Pointcuts
     *
     * @var \ArrayObject
     */
    public $pointcuts = array();

    /**
     * Binding definition
     *
     * @var Definition
     */
    public $bindings;

    /**
     * Object carry container
     *
     * @var \ArrayObject
     */
    public $container;

    /**
     * Params
     *
     * @var array
     */
    protected $params = array();

    /**
     * Current Binding
     *
     * @var string
     */
    protected $currentBinding;

    /**
     * Current Name
     *
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
        if (is_null($module)) {
            $this->bindings = new \ArrayObject();
            $this->annotations = new \ArrayObject();
            $this->pointcuts = new \ArrayObject();
            $this->container = new \ArrayObject();
        } else {
            $this->bindings = $module->bindings;
            $this->pointcuts = $module->pointcuts;
            $this->annotations = $module->annotations;
            $this->container = $module->container;
        }
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
     * @throws Exception\InvalidToBinding
     */
    protected function to($class)
    {
        if (class_exists($class) === false) {
            throw new Exception\InvalidToBinding($class);
        }
        $this->bindings[$this->currentBinding][$this->currentName] = array(self::TO => array(self::TO_CLASS, $class));
        return $this;
    }

    /**
     * To provider
     *
     * @param string $provider provider class
     *
     * @return AbstractModule
     * @throws Exception\InvalidProviderBinding
     */
    protected function toProvider($provider)
    {
        $hasProviderInterface = class_exists($provider)
        && in_array('Ray\Di\ProviderInterface', class_implements($provider));
        if ($hasProviderInterface === false) {
            throw new Exception\InvalidProviderBinding($provider);
        }
        $this->bindings[$this->currentBinding][$this->currentName]
            = array(self::TO => array(self::TO_PROVIDER, $provider));
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
        $this->bindings[$this->currentBinding][$this->currentName]
            = array(self::TO => array(self::TO_INSTANCE, $instance));
    }

    /**
     * To closure
     *
     * @param Closure $closure
     *
     * @return void
     */
    protected function toClosure(\Closure $closure)
    {
        $this->bindings[$this->currentBinding][$this->currentName]
            = array(self::TO => array(self::TO_CLOSURE, $closure));
    }

    /**
     * Register intercepter annotation
     *
     * @param string $annotation   annotation
     * @param array  $interceptors Interceptor[]
     *
     * @return void
     */
    protected function registerInterceptAnnotation($annotation, array $interceptors)
    {
        $this->annotations[$annotation] = $interceptors;
    }

    /**
     * Bind interceptor
     *
     * @param \Closure $classMatcher
     * @param \Closure $methodMatcher
     * @param array    $interceptors
     *
     * @return void
     */
    protected function bindInterceptor($classMatcher, $methodMatcher, array $interceptors)
    {
        $this->pointcuts[] = array($classMatcher, $methodMatcher, $interceptors);
    }

    /**
     * Invoke
     *
     * @param string $class
     *
     * @return \Ray\Aop\Bind|boolean
     */
    public function __invoke($class)
    {
        foreach ($this->pointcuts as $pointcut) {
            list($classMatcher, $methodMatcher, $interceptors) = $pointcut;
            if ($classMatcher($class) === true) {
                $bind = new Bind();
                $bind->bindMatcher($methodMatcher, $interceptors);
                return $bind;
            }
        }
        return false;
    }

    /**
     * ArrayAccess::offsetExists
     *
     * @param offset
     */
    public function offsetExists($offset)
    {
        return isset($this->bindings[$offset]);
    }

    /**
     * ArrayAccess::offsetGet
     *
     * @param offset
     */
    public function offsetGet($offset)
    {
        return isset($this->bindings[$offset]) ? $this->bindings[$offset] : null;
    }

    /**
     * ArrayAccess::offsetSet
     *
     * @param offset
     * @param value
     * @throws Exception\ReadOnly
     */
    public function offsetSet($offset, $value)
    {
        throw new Exception\ReadOnly;
    }

    /**
     * ArrayAccess::offsetUnset
     *
     * @param offset
     * @throws Exception\ReadOnly
     */
    public function offsetUnset($offset)
    {
        throw new Exception\ReadOnly;
    }

    /**
     * Return binding information
     *
     * @return string
     */
    public function __toString()
    {
        $output = '';
        foreach ((array)$this->bindings as $bind => $bindTo) {
            foreach ($bindTo as $annoatte => $to) {
                $type = $to['to'][0];
                $output .= ($annoatte !== '*') ? "bind('{$bind}')->annotatedWith('{$annoatte}')" : "bind('{$bind}')";
                if ($type === 'class') {
                    $output .= "->to('" . $to['to'][1] . "')\n";
                }
                if ($type === 'instance') {
                    $instance = $to['to'][1];
                    $type = gettype($instance);
                    switch ($type) {
                        case "object":
                            $instance = '(object)' . get_class($instance);
                            break;
                        case "array":
                            $instance = '(array)' . json_encode(array_keys($instance));
                            break;
                        default:
                            $instance = "($type)$instance";
                    }
                    $output .= "->toInstance(" . $instance . ")\n";
                }
            }
        }
        return $output;
    }
}