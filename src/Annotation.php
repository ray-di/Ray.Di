<?php
/**
 * Ray
 *
 * @license  http://opensource.org/licenses/bsd-license.php BSD
 */
namespace Ray\Di;

use Doctrine\Common\Annotations\DocParser,
    Doctrine\Common\Annotations\PhpParser,
    Doctrine\Common\Annotations\Annotation\Target;

/**
 * Annotation scanner
 *
 * @package Ray.Di
 * @author  Akihito Koriyama <akihito.koriyama@gmail.com>
 */
class Annotation implements AnnotationInterface
{
    /**
     * Class definition
     *
     * @var Definition
     */
    protected $definition;

    /**
     * Ray.Di Annotations
     *
     * @var array
     */
    private $defaultImports = [
        'aspect' => 'Ray\Di\Di\Aspect',
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
     */
    public function __construct(Definition $definition)
    {
        $this->definition = $definition;
        $this->docParser = new DocParser;
        $this->docParser->setIgnoreNotImportedAnnotations(true);
        $this->phpParser = new PhpParser;
    }

    /**
     * Get class definition by annotation
     *
     * @param string $class
     *
     * @return array
     */
    public function getDefinition($className)
    {
        $this->definition = [];
        $class = new \ReflectionClass($className);
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
        return $this->definition;
    }

    /**
	 * Get definition format from annotations
	 *
	 * @param array $annotations
	 *
	 * @return [$annotation => $value][]
     */
    private function getDefinitionFormat(array $annotations)
    {
        $result = [];
        foreach ($annotations as $annotation) {
            $classPath = explode('\\', get_class($annotation));
            $key = array_pop($classPath);
            $value = isset($annotation->value) ? $annotation->value : null ;
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
            /** @var \ReflectionMethod $method */
            $this->docParser->setTarget(Target::TARGET_METHOD);
            $annotations = $this->docParser->parse($method->getDocComment(), 'class ' . $class->name);
            $methodAnnotation = $this->getDefinitionFormat($annotations);
            foreach ($methodAnnotation as $key => $value) {
                $this->setAnnotationKey($key, $value, $methodAnnotation, $method);
            }
            $this->docParser->setTarget(Target::TARGET_CLASS);
            foreach ($annotations as $annotation) {
                $annotationClass = new \ReflectionClass($annotation);
                $annotationClassAnnotations = $this->docParser->parse($annotationClass->getDocComment(), 'class ' . $annotationClass->name);
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
                throw new Exception\MultipleAnnotationNotAllowed();
            } else {
                $this->definition[$key] = $method->name;
            }
            return;
        }
        if ($key === Definition::INJECT) {
            $this->setSetterInjectDefinition($methodAnnotation, $method);
            return;
        }
        if ($key === Definition::NAMED) {
            return;
        }
        $this->definition[Definition::BINDING][$key][] = [$method->name, $methodAnnotation];
    }

    /**
     * Set setter inject definition
     *
     * @param array             $methodAnnotation
     * @param \ReflectionMethod $method
     *
     * @return void
     */
    private function setSetterInjectDefinition(array $methodAnnotation,\ReflectionMethod $method)
    {
        $nameParameter = isset($methodAnnotation[Definition::NAMED]) ? $methodAnnotation[Definition::NAMED] : false;
        $named = ($nameParameter !== false) ? $this->getNamed($nameParameter) : [];
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
            $this->docParser->setTarget(Target::TARGET_CLASS);
            $doc = (new \ReflectionClass($typehint))->getDocComment();
            $annotations = $this->docParser->parse($doc, 'class ' . $typehint);
            $hintDef = $this->getDefinitionFormat($annotations);
            $definition[$typehint] = $hintDef;
        }
        // @ImplementBy as default
        if (isset($hintDef[Definition::IMPLEMENTEDBY])) {
            $result = array(Definition::PARAM_TYPEHINT_METHOD_IMPLEMETEDBY, $hintDef[Definition::IMPLEMENTEDBY]);
            return $result;
        }
        // @ProvidBY as default
        if (isset($hintDef[Definition::PROVIDEDBY])) {
            $result = array(Definition::PARAM_TYPEHINT_METHOD_PROVIDEDBY, $hintDef[Definition::PROVIDEDBY]);
            return $result;
        }
        // this typehint is class, not a interface.
        if (class_exists($typehint)) {
            $class = new \ReflectionClass($typehint);
            if ($class->isAbstract() === false) {
                $result = array(Definition::PARAM_TYPEHINT_METHOD_IMPLEMETEDBY, $typehint);
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
     * @throws Exception\InvalidNamed
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
            throw new Exception\InvalidNamed;
        }
        $result = [];
        $count = count($matches[0]);
        for ($i = 0; $i < $count; $i++) {
            $result[$matches[1][$i]] = $matches[2][$i];
        }
        return $result;
    }
}