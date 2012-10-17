<?php
/**
 * This file is part of the Ray package.
 *
 * @package Ray.Di
 * @license http://opensource.org/licenses/bsd-license.php BSD
 */
namespace Ray\Di;

use Doctrine\Common\Annotations\Reader;
use ReflectionClass;
use ReflectionMethod;

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
     */
    public function __construct(Definition $definition, Reader $reader)
    {
        $this->newDefinition = $definition;
        $this->reader = $reader;
    }

    /**
     * Return class definition by annotation
     *
     * @param string $className
     *
     * @return array
     * @throws Exception\NotReadable
     */
    public function getDefinition($className)
    {
        if (isset($this->definitions[$className])) {
            return $this->definitions[$className];
        }
        $this->definition = clone $this->newDefinition;
        try {
            $class = new ReflectionClass($className);
        } catch (\ReflectionException $e) {
            throw new Exception\NotReadable($className, 0, $e);
        }
        $annotations = $this->reader->getClassAnnotations($class);
        $classDefinition = $this->getDefinitionFormat($annotations);
        foreach ($classDefinition as $key => $value) {
            $this->definition[$key] = $value;
        }
        // Method Annotation
        $this->setMethodDefinition($class);
        $this->definitions[$className] = $this->definition;

        return $this->definition;
    }

    /**
     * Return definition format from annotations
     *
     * @param array $annotations
     * @param bool  $returnValue
     *
     * @return array [$annotation => $value][]
     */
    private function getDefinitionFormat(array $annotations, $returnValue = true)
    {
        $result = [];
        foreach ($annotations as $annotation) {
            $annotationName = $this->getAnnotationName($annotation);
            $value = $annotation;
            if ($returnValue === true) {
                $value = isset($annotation->value) ? $annotation->value : null;
            }
            $result[$annotationName] = $value;
        }

        return $result;
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
            $methodAnnotation = $this->getDefinitionFormat($annotations, false);
            foreach ($methodAnnotation as $key => $value) {
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
            } else {
                $this->definition[$name] = $method->name;
            }

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
        $paramsInfo = [];
        foreach ($parameters as $parameter) {
            /** @var $parameter \ReflectionParameter */
            $class = $parameter->getClass();
            $typehint = $class ? $class->getName() : '';
            $typehintBy = $typehint ? $this->getTypeHintDefaultInjection($typehint) : [];
            $pos = $parameter->getPosition();
            if (is_string($named)) {
                $name = $named;
            } elseif (isset($named[$parameter->name])) {
                $name = $named[$parameter->name];
            } else {
                $name = Definition::NAME_UNSPECIFIED;
            }
            $optionalInject = $methodAnnotation[Definition::INJECT]->optional;
            $paramsInfo[] = [
                Definition::PARAM_POS => $pos,
                Definition::PARAM_TYPEHINT => $typehint,
                Definition::PARAM_NAME => $parameter->name,
                Definition::PARAM_ANNOTATE => $name,
                Definition::PARAM_TYPEHINT_BY => $typehintBy,
                Definition::OPTIONAL => $optionalInject
            ];
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
        static $definition = [];

        if (isset($definition[$typehint])) {
            $hintDef = $definition[$typehint];
        } else {
            //$annotations = $this->docParser->parse($doc, 'class ' . $typehint);
            $annotations = $this->reader->getClassAnnotations(new ReflectionClass($typehint));
            $hintDef = $this->getDefinitionFormat($annotations);
            $definition[$typehint] = $hintDef;
        }
        // @ImplementBy as default
        if (isset($hintDef[Definition::IMPLEMENTEDBY])) {
            $result = [Definition::PARAM_TYPEHINT_METHOD_IMPLEMETEDBY, $hintDef[Definition::IMPLEMENTEDBY]];

            return $result;
        }
        // @ProvidedBy as default
        if (isset($hintDef[Definition::PROVIDEDBY])) {
            $result = [Definition::PARAM_TYPEHINT_METHOD_PROVIDEDBY, $hintDef[Definition::PROVIDEDBY]];

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
}
