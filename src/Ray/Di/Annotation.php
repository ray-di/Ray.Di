<?php
/**
 * Ray
 *
 * @package Ray.Di
 * @license http://opensource.org/licenses/bsd-license.php BSD
 */
namespace Ray\Di;

use Doctrine\Common\Annotations\DocParser;
use Doctrine\Common\Annotations\PhpParser;
use Doctrine\Common\Annotations\Annotation\Target;
use ReflectionClass;
use ReflectionMethod;

/**
 * Annotation scanner
 *
 * @package Ray.Di
 * @author  Akihito Koriyama <akihito.koriyama@gmail.com>
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
     * Ray.Di Annotations
     *
     * @var array
     */
    private $defaultImports = [
        'bindingannotation' => 'Ray\Di\Di\BindingAnnotation',
        'implementedby' => 'Ray\Di\Di\ImplementedBy',
        'inject' => 'Ray\Di\Di\Inject',
        'named' => 'Ray\Di\Di\Named',
        'postconstruct' => 'Ray\Di\Di\PostConstruct',
        'predestroy' => 'Ray\Di\Di\PreDestroy',
        'providedby' => 'Ray\Di\Di\ProvidedBy',
        'scope' => 'Ray\Di\Di\Scope'
    ];

    /**
     * Constructor
     *
     * @param Definition $definition
     */
    public function __construct(Definition $definition)
    {
        $this->docParser = new DocParser;
        $this->docParser->setIgnoreNotImportedAnnotations(true);
        $this->phpParser = new PhpParser;
        $this->newDefinition = $definition;
    }

    /**
     * Get class definition by annotation
     *
     * @param string $className
     *
     * @return array
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
            throw new Exception\NotReadable($className, 0 , $e);
        }
        $useImports = $this->phpParser->parseClass($class);
        $imports = array_merge($this->defaultImports, $useImports);
        $this->docParser->setImports($imports);
        // Class Annoattion
        $this->docParser->setTarget(Target::TARGET_CLASS);
        $annotations = $this->docParser->parse($class->getDocComment(), 'class ' . $class->name);
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
     * Get definition format from annotations
     *
     * @param array $annotations
     *
     * @return [$annotation => $value][]
     */
    private function getDefinitionFormat(array $annotations, $returnValue = true)
    {
        $result = [];
        foreach ($annotations as $annotation) {
            $classPath = explode('\\', get_class($annotation));
            $key = array_pop($classPath);
            $value = $annotation;
            if ($returnValue === true) {
                $value = isset($annotation->value) ? $annotation->value : null ;
            }
            $result[$key] = $value;
        }

        return $result;
    }

    /**
     * Set method definition
     *
     * @param \ReflectionClass $class
     *
     * @return void
     */
    private function setMethodDefinition(\ReflectionClass $class)
    {
        $methods = $class->getMethods();
        foreach ($methods as $method) {
            /** @var ReflectionMethod $method */
            $this->docParser->setTarget(Target::TARGET_METHOD);
            $annotations = $this->docParser->parse($method->getDocComment(), 'class ' . $class->name);
            $methodAnnotation = $this->getDefinitionFormat($annotations, false);
            foreach ($methodAnnotation as $key => $value) {
                $this->setAnnotationName($key, $method, $methodAnnotation);
            }
            // user land annotation by method
            foreach ($annotations as $annotation) {
                $classPath = explode('\\', get_class($annotation));
                $annotationName = array_pop($classPath);
                $this->definition->setUserAnnotationByMethod($annotationName, $method->name, $annotation);
            }
        }
    }

    /**
     * Set annotation key-value for DI
     *
     * @param string           $name        annotation name
     * @param ReflectionMethod $method
     * @param \array           $annotations
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
            /* @var $parameter ReflectionParameter */
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
            $optinalInject = $methodAnnotation[Definition::INJECT]->optional;
            $paramsInfo[] = [
                Definition::PARAM_POS => $pos,
                Definition::PARAM_TYPEHINT => $typehint,
                Definition::PARAM_NAME => $parameter->name,
                Definition::PARAM_ANNOTATE => $name,
                Definition::PARAM_TYPEHINT_BY => $typehintBy,
                Definition::OPTIONAL => $optinalInject
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
            $this->docParser->setTarget(Target::TARGET_CLASS);
            $doc = (new \ReflectionClass($typehint))->getDocComment();
            $annotations = $this->docParser->parse($doc, 'class ' . $typehint);
            $hintDef = $this->getDefinitionFormat($annotations);
            $definition[$typehint] = $hintDef;
        }
        // @ImplementBy as default
        if (isset($hintDef[Definition::IMPLEMENTEDBY])) {
            $result = [Definition::PARAM_TYPEHINT_METHOD_IMPLEMETEDBY, $hintDef[Definition::IMPLEMENTEDBY]];

            return $result;
        }
        // @ProvidBY as default
        if (isset($hintDef[Definition::PROVIDEDBY])) {
            $result = [Definition::PARAM_TYPEHINT_METHOD_PROVIDEDBY, $hintDef[Definition::PROVIDEDBY]];

            return $result;
        }
        // this typehint is class, not a interface.
        if (class_exists($typehint)) {
            $class = new \ReflectionClass($typehint);
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
     * @return array           [$paramName => $named][]
     * @throws Exception\Named
     */
    private function getNamed($nameParameter)
    {
        // signle annotation @Named($annotation)
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
