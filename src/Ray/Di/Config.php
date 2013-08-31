<?php
/**
 * This file is taken from Aura.Di(https://github.com/auraphp/Aura.Di) and modified.
 *
 * @package Ray.Di
 * @license http://opensource.org/licenses/bsd-license.php BSD
 * @see     https://github.com/auraphp/Aura.Di
 */
namespace Ray\Di;

use Aura\Di\ConfigInterface;
use ArrayObject;
use ReflectionClass;
use ReflectionMethod;
use Ray\Di\Di\Inject;

/**
 * Retains and unifies class configurations.
 *
 * @package Ray.Di
 */
class Config implements ConfigInterface
{
    /**
     * Parameter index number
     */
    const INDEX_PARAM = 0;

    /**
     * Setter index number
     */
    const INDEX_SETTER = 1;

    /**
     * Definition index number
     */
    const INDEX_DEFINITION = 2;

    /**
     *
     * Constructor params from external configuration in the form
     * `$params[$class][$name] = $value`.
     *
     * @var \ArrayObject
     *
     */
    protected $params;

    /**
     *
     * An array of retained ReflectionClass instances; this is as much for
     * the Forge as it is for Config.
     *
     * @var array
     *
     */
    protected $reflect = [];

    /**
     *
     * Setter definitions in the form of `$setter[$class][$method] = $value`.
     *
     * @var \ArrayObject
     *
     */
    protected $setter;

    /**
     *
     * Constructor params and setter definitions, unified across class
     * defaults, inheritance hierarchies, and external configurations.
     *
     * @var array
     *
     */
    protected $unified = [];

    /**
     * Method parameters
     *
     * $params[$class][$method] = [$param1varName, $param2varName ...]
     *
     * @var array
     */
    protected $methodReflect;

    /**
     * Class annotated definition. object life cycle, dependency injection.
     *
     * `$definition[$class]['Scope'] = $value`
     * `$definition[$class]['PostConstruct'] = $value`
     * `$definition[$class]['PreDestroy'] = $value`
     * `$definition[$class]['Inject'] = $value`
     *
     * @var Definition
     */
    protected $definition;

    /**
     * Annotation scanner
     *
     * @var AnnotationInterface
     */
    protected $annotation;

    /**
     * Constructor
     *
     * @param AnnotationInterface $annotation
     *
     * @Inject
     */
    public function __construct(AnnotationInterface $annotation)
    {
        $this->reset();
        $this->annotation = $annotation;
    }

    /**
     *
     * When cloning this object, reset the params and setter values (but
     * leave the reflection values in place).
     *
     * @return void
     *
     */
    public function __clone()
    {
        $this->reset();
    }

    /**
     *
     * Resets the params and setter values.
     *
     * @return void
     *
     */
    protected function reset()
    {
        $this->params = new ArrayObject;
        $this->params['*'] = [];
        $this->setter = new ArrayObject;
        $this->setter['*'] = [];
        $this->definition = new Definition([]);
        $this->definition['*'] = [];
        $this->methodReflect = new ArrayObject;
    }

    /**
     * {@inheritdoc}
     */
    public function getParams()
    {
        return $this->params;
    }

    /**
     * {@inheritdoc}
     */
    public function getSetter()
    {
        return $this->setter;
    }

    /**
     *
     * Gets the $definition property.
     *
     * @return Definition
     *
     */
    public function getDefinition()
    {
        return $this->definition;
    }

    /**
     * {@inheritdoc}
     */
    public function getReflect($class)
    {
        if (!isset($this->reflect[$class])) {
            $this->reflect[$class] = new ReflectionClass($class);
        }

        return $this->reflect[$class];
    }

    /**
     * {@inheritdoc}
     */
    public function fetch($class)
    {
        // have values already been unified for this class?
        if (isset($this->unified[$class])) {
            return $this->unified[$class];
        }

        // fetch the values for parents so we can inherit them
        $parentClass = get_parent_class($class);
        list($parent_params, $parent_setter, $parent_definition) =
        $parentClass ? $this->fetch($parentClass) : [$this->params['*'], $this->setter['*'], $this->annotation->getDefinition($class)];
        // stores the unified config and setter values

        $unified_params = [];

        // reflect on the class
        $classReflection = $this->getReflect($class);

        // does it have a constructor?
        $constructorReflection = $classReflection->getConstructor();
        if ($constructorReflection) {
            // reflect on what params to pass, in which order
            $params = $constructorReflection->getParameters();
            foreach ($params as $param) {
                $name = $param->name;
                $explicit = $this->params->offsetExists($class) && isset($this->params[$class][$name]);
                if ($explicit) {
                    // use the explicit value for this class
                    $unified_params[$name] = $this->params[$class][$name];
                    continue;
                } elseif (isset($parent_params[$name])) {
                    // use the implicit value for the parent class
                    $unified_params[$name] = $parent_params[$name];
                    continue;
                } elseif ($param->isDefaultValueAvailable()) {
                    // use the external value from the constructor
                    $unified_params[$name] = $param->getDefaultValue();
                    continue;
                }
                // no value, use a null placeholder
                $unified_params[$name] = null;
            }
        }
        // merge the setters
        $unified_setter = isset($this->setter[$class]) ? array_merge($parent_setter, $this->setter[$class]) : $parent_setter;

        // merge the definitions
        $definition = isset($this->definition[$class]) ? $this->definition[$class] : $this->annotation->getDefinition($class);
        /** @var $parent_definition \ArrayObject */
        $defaults = array_merge($parent_definition->getArrayCopy(), $definition->getArrayCopy());
        $unified_definition = new Definition($defaults);
        $this->definition[$class] = $unified_definition;

        // done, return the unified values
        $this->unified[$class][0] = $unified_params;
        $this->unified[$class][1] = $unified_setter;
        $this->unified[$class][2] = $unified_definition;

        return $this->unified[$class];
    }

    /**
     *
     * Returns a \ReflectionClass for a named class.
     *
     * @param string $class  The class to reflect on
     * @param string $method The method to reflect on
     *
     * @return \ReflectionMethod
     *
     */
    public function getMethodReflect($class, $method)
    {
        if (is_object($class)) {
            $class = get_class($class);
        }
        if (!isset($this->reflect[$class]) || !is_array($this->reflect[$class])) {
            $methodRef = new ReflectionMethod($class, $method);
            $this->methodReflect[$class][$method] = $methodRef;
        }

        return $this->methodReflect[$class][$method];
    }

    /**
     * Remove reflection property
     *
     * @return array
     */
    public function __sleep()
    {
        return ['params', 'setter', 'unified', 'definition', 'annotation'];
    }
}
