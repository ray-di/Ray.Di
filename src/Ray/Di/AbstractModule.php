<?php
/**
 * This file is part of the Ray package.
 *
 * @package Ray.Di
 * @license http://opensource.org/licenses/bsd-license.php BSD
 */
namespace Ray\Di;

use Ray\Aop\Bind;
use Ray\Aop\Matcher;
use Ray\Aop\Pointcut;
use Doctrine\Common\Annotations\AnnotationReader as Reader;
use ArrayObject;
use ArrayAccess;

/**
 * A module contributes configuration information, typically interface bindings,
 *  which will be used to create an Injector.
 *
 * @package Ray.Di
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
     * Binding definition
     *
     * @var Definition
     */
    public $bindings;

    /**
     * Pointcuts
     *
     * @var ArrayObject
     */

    /**
     * Object carry container
     *
     * @var ArrayObject
     */
    protected $container;

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
     * A list with annotations that are not causing exceptions when not resolved to an annotation class.
     *
     * The names are case sensitive.
     *
     * @var array
     */
    private $globalIgnoredNames = [
        'Inject',
        'Named',
        'ImplementedBy',
        'PostConstruct',
        'PreDestroy',
        'ProvidedBy',
        'Scope',
        'Provides'
    ];

    /**
     * Pointcuts
     *
     * @var array
     */
    public $pointcuts = [];

    /**
     * Has passed module data from other module ?
     * install() needs not to duplicate interceptor binding
     *
     * @var bool
     */
    private $isInherited = false;

    /**
     * Constructor
     *
     * @param AbstractModule $module
     */
    public function __construct(
        AbstractModule $module = null,
        Matcher $matcher = null
    ){
        if (is_null($module)) {
            $this->bindings = new ArrayObject;
            $this->pointcuts = new ArrayObject;
            $this->container = new ArrayObject;
        } else {
            $this->isInherited = true;
            $this->bindings = $module->bindings;
            $this->pointcuts = $module->pointcuts;
            $this->container = $module->container;
        }
        if (is_null($matcher)) {
            $reader = new Reader;
            foreach ($this->globalIgnoredNames as $name) {
                $reader->addGlobalIgnoredName($name);
            }
            $matcher = new Matcher($reader);
        }
        $this->matcher = $matcher;
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
     * @return $this
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
        $this->bindings[$this->currentBinding][$name] = [self::IN => Scope::SINGLETON];

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
     * @throws Exception\Configuration
     */
    protected function toProvider($provider)
    {
        $hasProviderInterface = class_exists($provider)
        && in_array('Ray\Di\ProviderInterface', class_implements($provider));
        if ($hasProviderInterface === false) {
            throw new Exception\Configuration($provider);
        }
        $this->bindings[$this->currentBinding][$this->currentName]
        = [self::TO => [self::TO_PROVIDER, $provider]];

        return $this;
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
        = [self::TO => [self::TO_INSTANCE, $instance]];
    }

    /**
     * To closure
     *
     * @param Closure $closure
     *
     * @return void
     */
    protected function toCallable(Callable $callable)
    {
        $this->bindings[$this->currentBinding][$this->currentName]
        = [self::TO => [self::TO_CALLABLE, $callable]];
    }

    protected function toConstructor(array $params)
    {
        $this->bindings[$this->currentBinding][$this->currentName]
        = [self::TO => [self::TO_CONSTRUCTOR, $params]];
    }

    /**
     * Bind interceptor
     *
     * @param Callable $classMatcher
     * @param Callable $methodMatcher
     * @param array    $interceptors
     *
     * @return void
     */
    protected function bindInterceptor(Callable $classMatcher, Callable $methodMatcher, array $interceptors)
    {
        $id = uniqid();
        $this->pointcuts[$id] = new Pointcut($classMatcher, $methodMatcher, $interceptors);
    }

    /**
     * Install module
     *
     * @params AbstractModule $module
     *
     * @return void
     */
    public function install(AbstractModule $module)
    {
        $class = get_class($module);
        if ($this->isInherited === true) {
            $this->pointcuts = $module->pointcuts;
        } else {
            $this->pointcuts = new ArrayObject(array_merge((array) $module->pointcuts, (array) $this->pointcuts));
        }
        $this->bindings = new ArrayObject(array_merge_recursive((array) $this->bindings, (array) $module->bindings));
        $this->container = new ArrayObject(array_merge_recursive((array) $module->container, (array) $this->container));
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
        $injector = Injector::create();
        $injector->setModule($this);

        return $injector->getInstance($class);
    }

    /**
     * Return matched binder.
     *
     * @param string $class
     *
     * @return \Ray\Aop\Bind
     */
    public function __invoke($class, Bind $bind)
    {
        $bind->bind($class, (array) $this->pointcuts);

        return $bind;
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
        foreach ((array) $this->bindings as $bind => $bindTo) {
            foreach ($bindTo as $annoatte => $to) {
                $type = $to['to'][0];
                $output .= ($annoatte !== '*') ? "bind('{$bind}')->annotatedWith('{$annoatte}')" : "bind('{$bind}')";
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
                            $instance = '(array) ' . json_encode(array_keys($instance));
                            break;
                        default:
                            $instance = "($type) $instance";
                    }
                    $output .= "->toInstance(" . $instance . ")";
                }
                if ($type === 'provider') {
                    $provider = $to['to'][1];
                    $output .= "->toProvider(" . $provider . ")";
                }
                $output .= PHP_EOL;
            }
        }

        return $output;
    }

    public function __sleep()
    {
        return ['bindings', 'pointcuts'];
    }
}
