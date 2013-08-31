<?php
/**
 * This file is part of the Ray package.
 *
 * @package Ray.Di
 * @license http://opensource.org/licenses/bsd-license.php BSD
 */
namespace Ray\Di;

use Doctrine\Common\Annotations\Reader;
use Ray\Di\Exception\NotReadable;
use ReflectionClass;
use ReflectionMethod;
use ReflectionParameter;
use Ray\Di\Di\Inject;

/**
 * Annotation scanner
 *
 * @package Ray.Di
 */
class Annotation implements AnnotationInterface
{
    /**
     * User defined annotation
     *
     * $definition[Annotation::USER][$methodName] = [$annotation1, $annotation2 .. ]
     *
     * @var array
     */
    const USER = 'user';

    /**
     * Class definition (new)
     *
     * @var Definition
     */
    protected $newDefinition;

    /**
     * Class definition
     *
     * @var Definition
     */
    protected $definition;

    /**
     * Class definitions for in-memory cache
     *
     * @var Definition[]
     */
    protected $definitions = [];

    /**
     * Annotation reader
     *
     * @var \Doctrine\Common\Annotations\Reader;
     */
    protected $reader;

    /**
     * Constructor
     *
     * @param Definition $definition
     * @param Reader     $reader
     *
     * @Inject
     */
    public function __construct(Definition $definition, Reader $reader)
    {
        $this->newDefinition = $definition;
        $this->reader = $reader;
    }

    /**
     * {@inheritdoc}
     */
    public function getDefinition($className)
    {
        if (! class_exists($className) && ! interface_exists($className)) {
            throw new NotReadable($className);
        }
        if (isset($this->definitions[$className])) {
            return $this->definitions[$className];
        }
        $this->definition = clone $this->newDefinition;
        $class = new ReflectionClass($className);
        $annotations = $this->reader->getClassAnnotations($class);
        $classDefinition = $this->getClassDefinition($annotations);
        foreach ($classDefinition as $key => $value) {
            $this->definition[$key] = $value;
        }
        // Method Annotation
        $this->setMethodDefinition($class);
        $this->definitions[$className] = $this->definition;

        return $this->definition;
    }

    /**
     * Return class definition from annotations
     *
     * @param Annotation[] $annotations
     *
     * @return array
     */
    private function getClassDefinition(array $annotations)
    {
        $result = [];
        foreach ($annotations as $annotation) {
            $annotationName = $this->getAnnotationName($annotation);
            $value = isset($annotation->value) ? $annotation->value : null;
            $result[$annotationName] = $value;
        }

        return $result;
    }

    /**
     * Return method definition from annotations
     *
     * @param Annotation[] $annotations
     *
     * @return array
     */
    private function getMethodDefinition(array $annotations)
    {
        $result = [];
        foreach ($annotations as $annotation) {
            $annotationName = $this->getAnnotationName($annotation);
            $value = $annotation;
            $result[$annotationName] = $value;
        }

        return $result;
    }

    /**
     * Return annotation name from annotation class name
     *
     * @param $annotation
     *
     * @return mixed
     */
    private function getAnnotationName($annotation)
    {
        $classPath = explode('\\', get_class($annotation));
        $annotationName = array_pop($classPath);

        return $annotationName;
    }

    /**
     * Set method definition
     *
     * @param ReflectionClass $class
     *
     * @return void
     */
    private function setMethodDefinition(ReflectionClass $class)
    {
        $methods = $class->getMethods();
        foreach ($methods as $method) {
            $annotations = $this->reader->getMethodAnnotations($method);
            $methodAnnotation = $this->getMethodDefinition($annotations);
            $keys = array_keys($methodAnnotation);
            foreach ($keys as $key) {
                $this->setAnnotationName($key, $method, $methodAnnotation);
            }
            // user land annotation by method
            foreach ($annotations as $annotation) {
                $annotationName = $this->getAnnotationName($annotation);
                $this->definition->setUserAnnotationByMethod($annotationName, $method->name, $annotation);
            }
        }
    }

    /**
     * Set annotation key-value for DI
     *
     * @param string           $name        annotation name
     * @param ReflectionMethod $method
     * @param array            $annotations
     *
     * @return void
     * @throws Exception\MultipleAnnotationNotAllowed
     */
    private function setAnnotationName($name, ReflectionMethod $method, array $annotations)
    {
        if ($name === Definition::POST_CONSTRUCT || $name == Definition::PRE_DESTROY) {
            if (isset($this->definition[$name]) && $this->definition[$name]) {
                $msg = "@{$name} in " . $method->getDeclaringClass()->name;
                throw new Exception\MultipleAnnotationNotAllowed($msg);
            }
            $this->definition[$name] = $method->name;

            return;
        }
        if ($name === Definition::INJECT) {
            $this->setSetterInjectDefinition($annotations, $method);

            return;
        }
        if ($name === Definition::NAMED) {
            return;
        }
        // user land annotation by name
        $this->definition->setUserAnnotationMethodName($name, $method->name);
    }

    /**
     * Set setter inject definition
     *
     * @param array            $methodAnnotation
     * @param ReflectionMethod $method
     *
     * @return void
     */
    private function setSetterInjectDefinition($methodAnnotation, ReflectionMethod $method)
    {
        $nameParameter = false;
        if (isset($methodAnnotation[Definition::NAMED])) {
            $named = $methodAnnotation[Definition::NAMED];
            $nameParameter = $named->value;
        }
        $named = ($nameParameter !== false) ? $this->getNamed($nameParameter) : [];
        $parameters = $method->getParameters();
        $paramInfo[$method->name] = $this->getParamInfo($methodAnnotation, $parameters, $named);
        $this->definition[Definition::INJECT][Definition::INJECT_SETTER][] = $paramInfo;
    }

    /**
     * @param ReflectionParameter[] $parameters
     *
     * @return array
     */

    /**
     * @param array $methodAnnotation
     * @param array $parameters
     * @param $named
     *
     * @return array
     */
    private function getParamInfo($methodAnnotation, array $parameters, $named)
    {
        $paramsInfo = [];
        foreach ($parameters as $parameter) {
            /** @var $parameter \ReflectionParameter */
            $class = $parameter->getClass();
            $typehint = $class ? $class->getName() : '';
            $typehintBy = $typehint ? $this->getTypeHintDefaultInjection($typehint) : [];
            $pos = $parameter->getPosition();
            $name = $this->getName($named, $parameter);
            $optionalInject = $methodAnnotation[Definition::INJECT]->optional;
            $definition = [
                Definition::PARAM_POS => $pos,
                Definition::PARAM_TYPEHINT => $typehint,
                Definition::PARAM_NAME => $parameter->name,
                Definition::PARAM_ANNOTATE => $name,
                Definition::PARAM_TYPEHINT_BY => $typehintBy,
                Definition::OPTIONAL => $optionalInject
            ];
            if ($parameter->isOptional()) {
                $definition[Definition::DEFAULT_VAL] = $parameter->getDefaultValue();
            }
            $paramsInfo[] = $definition;
        }

        return $paramsInfo;
    }

    /**
     * Return name
     *
     * @param mixed $named
     * @param $parameter
     *
     * @return string
     */
    private function getName($named, ReflectionParameter $parameter)
    {
        if (is_string($named)) {
            return $named;
        }
        if (is_array($named) && isset($named[$parameter->name])) {
            return $named[$parameter->name];
        }

        return Definition::NAME_UNSPECIFIED;
    }
    /**
     * Get Named
     *
     * @param string $nameParameter "value" or "key1=value1,ke2=value2"
     *
     * @return array [$paramName => $named][]
     * @throws Exception\Named
     */
    private function getNamed($nameParameter)
    {
        // single annotation @Named($annotation)
        if (preg_match("/^[a-zA-Z0-9_]+$/", $nameParameter)) {
            return $nameParameter;
        }
        // multi annotation @Named($varName1=$annotate1,$varName2=$annotate2)
        // http://stackoverflow.com/questions/168171/regular-expression-for-parsing-name-value-pairs
        preg_match_all('/([^=,]*)=("[^"]*"|[^,"]*)/', $nameParameter, $matches);
        if ($matches[0] === []) {
            throw new Exception\Named;
        }
        $result = [];
        $count = count($matches[0]);
        for ($i = 0; $i < $count; $i++) {
            $result[$matches[1][$i]] = $matches[2][$i];
        }

        return $result;
    }

    /**
     * Get default injection by typehint
     *
     * @param string $typehint
     *
     * @return array
     */
    private function getTypeHintDefaultInjection($typehint)
    {
        $annotations = $this->reader->getClassAnnotations(new ReflectionClass($typehint));
        $classDefinition = $this->getClassDefinition($annotations);

        // @ImplementBy as default
        if (isset($classDefinition[Definition::IMPLEMENTEDBY])) {
            $result = [Definition::PARAM_TYPEHINT_METHOD_IMPLEMETEDBY, $classDefinition[Definition::IMPLEMENTEDBY]];

            return $result;
        }
        // @ProvidedBy as default
        if (isset($classDefinition[Definition::PROVIDEDBY])) {
            $result = [Definition::PARAM_TYPEHINT_METHOD_PROVIDEDBY, $classDefinition[Definition::PROVIDEDBY]];

            return $result;
        }
        // this typehint is class, not a interface.
        if (class_exists($typehint)) {
            $class = new ReflectionClass($typehint);
            if ($class->isAbstract() === false) {
                $result = [Definition::PARAM_TYPEHINT_METHOD_IMPLEMETEDBY, $typehint];

                return $result;
            }
        }

        return [];
    }
}
