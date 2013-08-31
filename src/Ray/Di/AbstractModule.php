<?php
/**
 * This file is part of the Ray package.
 *
 * @package Ray.Di
 * @license http://opensource.org/licenses/bsd-license.php BSD
 */
namespace Ray\Di;

use ArrayAccess;
use ArrayObject;
use Doctrine\Common\Annotations\AnnotationReader as Reader;
use Ray\Aop\Bind;
use Ray\Aop\Matcher;
use Ray\Aop\Pointcut;

/**
 * A module contributes configuration information, typically interface bindings,
 *  which will be used to create an Injector.
 *
 * @package   Ray.Di
 */
abstract class AbstractModule implements ArrayAccess
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
    const TO_CALLABLE = 'callable';

    /**
     * To Constructor
     *
     * @var string
     */
    const TO_CONSTRUCTOR = 'constructor';

    /**
     * To Constructor
     *
     * @var string
     */
    const TO_SETTER = 'setter';

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
     * Binding
     *
     * @var ArrayObject
     */
    public $bindings;

    /**
     * Pointcuts
     *
     * @var ArrayObject
     */

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
    protected $scope = [Scope::PROTOTYPE, Scope::SINGLETON];

    /**
     * Pointcuts
     *
     * @var array
     */
    public $pointcuts = [];

    /**
     * @var InjectorInterface
     */
    protected $dependencyInjector;

    /**
     * Is activated
     *
     * @var bool
     */
    protected $activated = false;

    /**
     * Installed modules
     *
     * @var array
     */
    public $modules = [];

    /**
     * Constructor
     *
     * @param AbstractModule $module
     * @param Matcher        $matcher
     */
    public function __construct(
        AbstractModule $module = null,
        Matcher $matcher = null
    ) {
        $this->modules[] = get_class($this);
        $this->matcher = $matcher ? : new Matcher(new Reader);
        if (is_null($module)) {
            $this->bindings = new ArrayObject;
            $this->pointcuts = new ArrayObject;
            return;
        }
        $module->activate();
        $this->bindings = $module->bindings;
        $this->pointcuts = $module->pointcuts;
    }

    /**
     * Activation
     *
     * @param InjectorInterface $injector
     */
    public function activate(InjectorInterface $injector = null)
    {
        if ($this->activated === true) {
            return;
        }
        $this->activated = true;
        $this->dependencyInjector = $injector ? : Injector::create([$this]);
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
        if (strlen($interface) > 0 && $interface[0] === '\\') {
            // remove leading back slash
            $interface = substr($interface, 1);
        }

        $this->currentBinding = $interface;
        $this->currentName = self::NAME_UNSPECIFIED;

        return $this;
    }

    /**
     * Set binding annotation.
     *
     * @param string $name
     *
     * @return AbstractModule
     */
    protected function annotatedWith($name)
    {
        $this->currentName = $name;
        $this->bindings[$this->currentBinding][$name] = [self::IN => Scope::SINGLETON];

        return $this;
    }

    /**
     * Set scope
     *
     * @param string $scope
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
     * @throws Exception\ToBinding
     */
    protected function to($class)
    {
        $this->bindings[$this->currentBinding][$this->currentName] = [self::TO => [self::TO_CLASS, $class]];

        return $this;
    }

    /**
     * To provider
     *
     * @param string $provider provider class
     *
     * @return AbstractModule
     * @throws Exception\InvalidProvider
     */
    protected function toProvider($provider)
    {
        $hasProviderInterface = class_exists($provider) && in_array(
            'Ray\Di\ProviderInterface',
            class_implements($provider)
        );
        if ($hasProviderInterface === false) {
            throw new Exception\InvalidProvider($provider);
        }
        $this->bindings[$this->currentBinding][$this->currentName] = [self::TO => [self::TO_PROVIDER, $provider]];

        return $this;
    }

    /**
     * To instance
     *
     * @param mixed $instance
     *
     * @return AbstractModule
     */
    protected function toInstance($instance)
    {
        $this->bindings[$this->currentBinding][$this->currentName] = [self::TO => [self::TO_INSTANCE, $instance]];
    }

    /**
     * To closure
     *
     * @param callable $callable
     *
     * @return void
     */
    protected function toCallable(callable $callable)
    {
        $this->bindings[$this->currentBinding][$this->currentName] = [self::TO => [self::TO_CALLABLE, $callable]];
    }

    /**
     * To constructor
     *
     * @param array $params
     */
    protected function toConstructor(array $params)
    {
        $this->bindings[$this->currentBinding][$this->currentName] = [self::TO => [self::TO_CONSTRUCTOR, $params]];
    }

    /**
     * Bind interceptor
     *
     * @param Matcher $classMatcher
     * @param Matcher $methodMatcher
     * @param array   $interceptors
     *
     * @return void
     */
    protected function bindInterceptor(Matcher $classMatcher, Matcher $methodMatcher, array $interceptors)
    {
        $id = uniqid();
        $this->pointcuts[$id] = new Pointcut($classMatcher, $methodMatcher, $interceptors);
    }

    /**
     * Install module
     *
     * @param AbstractModule $module
     *
     * @return void
     */
    public function install(AbstractModule $module)
    {
        $module->activate($this->dependencyInjector);
        $this->pointcuts = new ArrayObject(array_merge((array)$module->pointcuts, (array)$this->pointcuts));
        $this->bindings = $this->mergeBindings($module);
        if ($module->modules) {
            $this->modules = array_merge($this->modules, $module->modules);
        }
    }

    /**
     * Merge binding
     *
     * @param AbstractModule $module
     *
     * @return ArrayObject
     */
    private function mergeBindings(AbstractModule $module)
    {
        return new ArrayObject($this->mergeArray((array)$this->bindings, (array)$module->bindings));
    }

    /**
     * Merge array recursive but not add array in same key like merge_array_recursive()
     *
     * @param array $origin
     * @param array $new
     *
     * @return array
     */
    private function mergeArray(array $origin, array $new)
    {
        foreach ($new as $key => $value) {
            $beMergeable = isset($origin[$key]) && is_array($value) && is_array($origin[$key]);
            $origin[$key] = $beMergeable ? $this->mergeArray($value, $origin[$key]) : $value;
        }

        return $origin;
    }

    /**
     * Request injection
     *
     * Get instance with current module.
     *
     * @param string $class
     *
     * @return object
     */
    public function requestInjection($class)
    {
        $module = $this->dependencyInjector->getModule();
        $this->dependencyInjector->setModule($this, false);
        $instance = $this->dependencyInjector->getInstance($class);
        if ($module instanceof AbstractModule) {
            $this->dependencyInjector->setModule($module, false);
        }

        return $instance;
    }

    /**
     * Return matched binder
     *
     * @param string $class
     * @param Bind   $bind
     *
     * @return Bind $bind
     */
    public function __invoke($class, Bind $bind)
    {
        $bind->bind($class, (array)$this->pointcuts);

        return $bind;
    }

    /**
     * ArrayAccess::offsetExists
     *
     * @param mixed $offset
     *
     * @return bool
     */
    public function offsetExists($offset)
    {
        return isset($this->bindings[$offset]);
    }

    /**
     * ArrayAccess::offsetGet
     *
     * @param string $offset
     *
     * @return mixed
     */
    public function offsetGet($offset)
    {
        return isset($this->bindings[$offset]) ? $this->bindings[$offset] : null;
    }

    /**
     * ArrayAccess::offsetSet
     *
     * @param string $offset
     * @param mixed  $value
     *
     * @throws Exception\ReadOnly
     */
    public function offsetSet($offset, $value)
    {
        throw new Exception\ReadOnly;
    }

    /**
     * ArrayAccess::offsetUnset
     *
     * @param string $offset
     *
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
            foreach ($bindTo as $annotate => $to) {
                $type = $to['to'][0];
                $output .= ($annotate !== '*') ? "bind('{$bind}')->annotatedWith('{$annotate}')" : "bind('{$bind}')";
                if ($type === 'class') {
                    $output .= "->to('" . $to['to'][1] . "')";
                }
                if ($type === 'instance') {
                    $instance = $to['to'][1];
                    $type = gettype($instance);
                    switch ($type) {
                        case "object":
                            $instance = '(object) ' . get_class($instance);
                            break;
                        case "array":
                            $instance = '(array) ' . json_encode($instance);
                            break;
                        case "string":
                            $instance = "'{$instance}'";
                            break;
                        case "boolean":
                            $instance = '(bool) ' . ($instance ? 'true' : 'false');
                            break;
                        default:
                            $instance = "($type) $instance";
                    }
                    $output .= "->toInstance(" . $instance . ")";
                }
                if ($type === 'provider') {
                    $provider = $to['to'][1];
                    $output .= "->toProvider('" . $provider . "')";
                }
                $output .= PHP_EOL;
            }
        }

        return $output;
    }

    /**
     * Keep only bindings and pointcuts.
     *
     * @return array
     */
    public function __sleep()
    {
        return ['bindings', 'pointcuts'];
    }
}
