<?php
/**
 * This file is part of the Ray package.
 *
 * @package Ray.Di
 * @license http://opensource.org/licenses/bsd-license.php BSD
 */
namespace Ray\Di;

use ArrayObject;

/**
 * Retains target class inject definition.
 *
 * @package Ray.Di
 */
class Definition extends ArrayObject
{
    /**
     * Post construct annotation
     *
     * @var string
     */
    const POST_CONSTRUCT = "PostConstruct";

    /**
     * PreDestroy annotation
     *
     * @var string
     */
    const PRE_DESTROY = "PreDestroy";

    /**
     * Inject annotation
     *
     * @var string
     */
    const INJECT = "Inject";

    /**
     * Provide annotation
     *
     * @var string
     */
    const PROVIDE = "Provide";

    /**
     * Scope annotation
     *
     * @var string
     */
    const SCOPE = "Scope";

    /**
     * ImplementedBy annotation (Just-in-time Binding)
     *
     * @var string
     */
    const IMPLEMENTEDBY = "ImplementedBy";

    /**
     * ProvidedBy annotation (Just-in-time Binding)
     *
     * @var string
     */
    const PROVIDEDBY = "ProvidedBy";

    /**
     * Named annotation
     *
     * @var string
     */
    const NAMED = "Named";

    /**
     * PreDestroy annotation
     *
     * @var string
     */
    const NAME_UNSPECIFIED = '*';

    /**
     * Setter inject definition
     *
     * @var string
     */
    const INJECT_SETTER = 'setter';

    /**
     * Parameter position
     *
     * @var string
     */
    const PARAM_POS = 'pos';

    /**
     * Typehint
     *
     * @var string
     */
    const PARAM_TYPEHINT = 'typehint';

    /**
     * Param typehint default concrete class / provider class
     *
     * @var array [$typehintMethod, $className>]
     */
    const PARAM_TYPEHINT_BY = 'typehint_by';

    /**
     * Param typehint default concrete class
     *
     * @var string
     */
    const PARAM_TYPEHINT_METHOD_IMPLEMETEDBY = 'implementedby';

    /**
     * Param typehint default provider
     *
     * @var string
     */
    const PARAM_TYPEHINT_METHOD_PROVIDEDBY = 'providedby';

    /**
     * Param var name
     *
     * @var string
     */
    const PARAM_NAME = 'name';

    /**
     * Param named annotation
     *
     * @var string
     */
    const PARAM_ANNOTATE = 'annotate';

    /**
     * Aspect annotation
     *
     * @var string
     */
    const ASPECT = 'Aspect';

    /**
     * User defined interceptor annotation
     *
     * @var string
     */
    const USER = 'user';

    /**
     * OPTIONS
     *
     * @var string
     */
    const OPTIONS = 'options';

    /**
     * BINDING
     *
     * @var string
     */
    const BINDING = 'binding';

    /**
     * BY_METHOD
     *
     * @var string
     */
    const BY_METHOD = 'by_method';

    /**
     * BY_NAME
     *
     * @var string
     */
    const BY_NAME = 'by_name';

    /**
     * Optional Inject
     *
     * @var string
     */
    const OPTIONAL = 'optional';

    /**
     * Definition default
     *
     * @var array
     */
    private $defaults = [
        self::SCOPE => Scope::PROTOTYPE,
        self::POST_CONSTRUCT => null,
        self::PRE_DESTROY => null,
        self::INJECT => [],
        self::IMPLEMENTEDBY => [],
        self::USER => [],
        self::OPTIONAL => []
    ];

    /**
     * Constructor
     *
     * @param array $defaults default definition set
     */
    public function __construct(array $defaults = null)
    {
        $defaults = $defaults ? : $this->defaults;
        parent::__construct($defaults);
    }

    /**
     * Return is-defined
     *
     * @return bool
     */
    public function hasDefinition()
    {
        $hasDefinition = ($this->getArrayCopy() !== $this->defaults);

        return $hasDefinition;
    }

    /**
     * Set user annotation by name
     *
     * @param string $annotationName
     * @param string $methodName
     *
     * @return void
     */
    public function setUserAnnotationMethodName($annotationName, $methodName)
    {
        $this[self::BY_NAME][$annotationName][] = $methodName;
    }

    /**
     * Return user annotation by annotation name
     *
     * @param $annotationName
     *
     * @return array [$methodName, $methodAnnotation]
     */
    public function getUserAnnotationMethodName($annotationName)
    {
        $hasUserAnnotation = isset($this[self::BY_NAME]) && isset($this[self::BY_NAME][$annotationName]);
        $result = $hasUserAnnotation ? $this[Definition::BY_NAME][$annotationName] : null;

        return $result;
    }

    /**
     * setUserAnnotationByMethod
     *
     * @param string $annotationName
     * @param string $methodName
     * @param object $methodAnnotation
     *
     * @return void
     */
    public function setUserAnnotationByMethod($annotationName, $methodName, $methodAnnotation)
    {
        $this[self::BY_METHOD][$methodName][$annotationName][] = $methodAnnotation;
    }

    /**
     * Return user annotation by method name
     *
     * @param string $methodName
     *
     * @return array [$annotationName, $methodAnnotation][]
     */
    public function getUserAnnotationByMethod($methodName)
    {
        $result = isset($this[self::BY_METHOD]) && isset($this[self::BY_METHOD][$methodName]) ?
            $this[self::BY_METHOD][$methodName] : null;

        return $result;
    }

    /**
     * Return class annotation definition information.
     *
     * @return string
     */
    public function __toString()
    {
        return var_export($this, true);
    }
}
