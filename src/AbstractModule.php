<?php
/**
 * Ray
 *
 * @package Ray.Di
 * @license http://opensource.org/licenses/bsd-license.php BSD
 */
namespace Ray\Di;

use Ray\Aop\Bind,
    Ray\Aop\Matcher;
use Doctrine\Common\Annotations\AnnotationReader as Reader;
use Doctrine\Common\Annotations\FileCacheReader;
use Doctrine\Common\Cache\ApcCache;
use ArrayObject;

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
    public $annotations;

    /**
     * Pointcuts
     *
     * @var \ArrayObject
     */
    public $pointcuts;

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
     * Constructor
     *
     * @param AbstractModule $module
     */
    public function __construct(AbstractModule $module = null)
    {
        if (is_null($module)) {
            $this->bindings = new \ArrayObject;
            $this->pointcuts = new \ArrayObject;
            $this->container = new \ArrayObject;
        } else {
            $this->bindings = $module->bindings;
            $this->pointcuts = $module->pointcuts;
            $this->container = $module->container;
        }
        $reader = new Reader;
        foreach ($this->globalIgnoredNames as $name) {
            $reader->addGlobalIgnoredName($name);
        }
        $reader = new FileCacheReader(
                $reader,
                dirname(__DIR__) . '/tmp/',
                $debug = true
        );
        $this->matcher = new Matcher($reader);
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
     * @throws Exception\InvalidToBinding
     */
    protected function to($class)
    {
        if (class_exists($class) === false) {
            throw new Exception\InvalidToBinding($class);
        }
        $this->bindings[$this->currentBinding][$this->currentName] = [self::TO => [self::TO_CLASS, $class]];
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
    protected function toClosure(\Closure $closure)
    {
        $this->bindings[$this->currentBinding][$this->currentName]
        = [self::TO => [self::TO_CLOSURE, $closure]];
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
    protected function bindInterceptor(Callable $classMatcher, Callable $methodMatcher, array $interceptors)
    {
        $this->pointcuts[] = [$classMatcher, $methodMatcher, $interceptors];
    }

    /**
	 * Install module
	 *
	 * @params AbstractModule $module
	 *
	 * @return void
     */
    protected function install(AbstractModule $module)
    {
        $this->bindings = new ArrayObject(array_merge($this->bindings->getArrayCopy(), $module->bindings->getArrayCopy()));
        $this->pointcuts = new ArrayObject(array_merge($module->pointcuts->getArrayCopy(), $module->pointcuts->getArrayCopy()));
        $this->container = new ArrayObject(array_merge($module->container->getArrayCopy(), $module->container->getArrayCopy()));
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
        $bind->bind($class, $this->pointcuts);
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

    public function __sleep()
    {
        return ['bindings'];
    }
}
