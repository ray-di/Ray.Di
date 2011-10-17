<?php
/**
 * Ray
 *
 * @license  http://opensource.org/licenses/bsd-license.php BSD
 */
namespace Ray\Di;

/**
 * Annotation scanner
 *
 * @package Ray.Di
 */
class Annotation implements AnnotationInterface
{
    /**
     * Class definition data
     *
     * @var array
     */
    protected $definition = array();

    /**
     * Method reflection
     *
     * @var ReflectionMethod
     */
    protected $methodReflection;

    /**
     * Config
     *
     * @var Config
     */
    protected $config;

    /**
     *  Set config
     *
     * @param ConfigInterface $config
     *
     * @return void
     */
    public function setConfig(ConfigInterface $config)
    {
        $this->config = $config;
    }

    /**
     * Get class definition by annotation
     *
     * @param string $class
     *
     * @return array
     */
    public function getDefinition($class)
    {
        $classRef = $this->config->getReflect($class);
        $this->definition = $this->getAnnotationByDoc($classRef->getDocComment());
        $this->parseMethods($classRef);
        return $this->definition;
    }

    /**
     * Parse method doc-comments
     *
     * @param \ReflectionClass $ref
     *
     * @return void
     */
    private function parseMethods(\ReflectionClass $ref)
    {
        $methods = $ref->getMethods();
        foreach ($methods as $method) {
            $this->methodReflection[$method->name] = $method;
            $doc = $method->getDocComment();
            $methodAnnotation = $this->getAnnotationByDoc($doc);
            foreach ($methodAnnotation as $key => $value) {
                $this->setAnnotationKey($key, $value, $methodAnnotation, $method);
            }
        }
    }

    /**
     * Set annotation key-value
     *
     * @param string            $key
     * @param mixed             $value
     * @param array             $methodAnnotation
     * @param \ReflectionMethod $method
     *
     * @return void
     * @throws Exception\MultipleAnnotationNotAllowed
     */
    private function setAnnotationKey($key, $value, array $methodAnnotation, \ReflectionMethod $method)
    {
        if ($key === Definition::POST_CONSTRUCT || $key == Definition::PRE_DESTROY) {
            if (isset($this->definition[$key])) {
                throw new Exception\MultipleAnnotationNotAllowed;
            } else {
                $this->definition[$key] = $method->name;
            }
            return;
        }
        if ($key === Definition::INJECT) {
            $this->setSetterInjectDefinition($methodAnnotation, $method);
        }
    }

    /**
     * Set setter inject definition
     *
     * @param array             $methodAnnotation
     * @param \ReflectionMethod $method
     *
     * @return void
     */
    private function setSetterInjectDefinition(array $methodAnnotation, \ReflectionMethod $method)
    {
        $nameParameter = isset($methodAnnotation[Definition::NAMED]) ? $methodAnnotation[Definition::NAMED] : false;
        $named = ($nameParameter !== false) ? $this->getNamed($nameParameter) : array();
        $parameters = $method->getParameters();
        $paramsInfo = array();
        foreach ($parameters as $parameter) {
            /* @var $parameter ReflectionParameter */
            $class = $parameter->getClass();
            $typehint = $class ? $class->getName() : '';
            $typehintBy = $typehint ? $this->getTypeHintDefaultInjection($typehint) : array();
            $pos = $parameter->getPosition();
            if (is_string($named)) {
                $name = $named;
            } elseif (isset($named[$parameter->name])) {
                $name = $named[$parameter->name];
            } else {
                $name = Definition::NAME_UNSPECIFIED;
            }
            $paramsInfo[] = array(
                Definition::PARAM_POS => $pos,
                Definition::PARAM_TYPEHINT => $typehint,
                Definition::PARAM_NAME => $parameter->name,
                Definition::PARAM_ANNOTATE => $name,
                Definition::PARAM_TYPEHINT_BY => $typehintBy
            );
        }
        $paramInfo[$method->name] = $paramsInfo;
        $this->definition[Definition::INJECT][Definition::INJECT_SETTER][] = $paramInfo;
    }

    /**
     * Get default injection by typehint
     *
     * this works as default bindings.
     *
     * @param string $typehint
     *
     * @return array
     */
    private function getTypeHintDefaultInjection($typehint)
    {
        static $definition = array();

        if (isset($definition[$typehint])) {
            $hintDef = $definition[$typehint];
        } else {
            $hintDef = $this->getAnnotationByDoc($this->config->getReflect($typehint)->getDocComment());
            $definition[$typehint] = $hintDef;
        }
        if (isset($hintDef[Definition::IMPLEMENTEDBY])) {
            $result = array(Definition::PARAM_TYPEHINT_METHOD_IMPLEMETEDBY, $hintDef[Definition::IMPLEMENTEDBY]);
            return $result;
        }
        if (isset($hintDef[Definition::PROVIDEDBY])) {
            $result = array(Definition::PARAM_TYPEHINT_METHOD_PROVIDEDBY, $hintDef[Definition::PROVIDEDBY]);
            return $result;
        }
        return array();
    }

    /**
     * Get Named
     *
     * @param string $nameParameter "value" or "key1=value1,ke2=value2"
     *
     * @return array <arary($paramName => $named)>
     */
    private function getNamed($nameParameter)
    {
        if (preg_match("/^[a-zA-Z0-9_]+$/", $nameParameter)) {
            return $nameParameter;
            // signle annotation @Named($annotation)
        }
        // multi annotation @Named($varName1=$annotate1,$varName2=$annotate2)
        // http://stackoverflow.com/questions/168171/regular-expression-for-parsing-name-value-pairs
        preg_match_all('/([^=,]*)=("[^"]*"|[^,"]*)/', $nameParameter, $matches);
        if ($matches[0] === array()) {
            throw new Exception\InvalidNamed;
        }
        $result = array();
        $count = count($matches[0]);
        for ($i = 0; $i < $count; $i++) {
            $result[$matches[1][$i]] = $matches[2][$i];
        }
        return $result;
    }

    /**
     * Get annotation array by doc-comments
     *
     * @return array
     */
    private function getAnnotationByDoc($doc)
    {
        $result = $match = array();
        preg_match_all('/@([A-Z][A-Za-z]+)(\("(.+)"\))*/', $doc, $match);
        $keys = $match[1];
        $values = $match[3];
        $i = 0;
        foreach ($keys as $key) {
            $result[$key] = $values[$i];
            $i++;
        }
        return $result;
    }
}