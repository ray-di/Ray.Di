<?php
/**
 *
 * This file is part of the Aura Project for PHP.
 *
 * @license http://opensource.org/licenses/bsd-license.php BSD
 *
 */
namespace Ray\Di;

/**
 *
 * Retains target class inject definition.
 *
 * @package Aura.Di
 *
 */
class Definition extends \ArrayObject
{
    /**
     * Postconstruct annotation
     *
     * @var string
     */
    const POST_CONSTRUCT = "PostConstruct";

    /**
     * PreDestoroy annotation
     *
     * @var string
     */
    const PRE_DESTROY = "PreDestoroy";

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
     * PreDestoroy annotation
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
     * @var array array($typehitMethod, $className>)
     */
    const PARAM_TYPEHINT_BY = 'typehint_by';

    /**
     * Param typehint default cocrete class
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
     * Array container
     *
     * @var array
     */
    private $container = array();

    /**
     * Default
     *
     * @var unknown_type
     */
    private $defaults = array();

    /**
     * Constructor
     */
    public function __construct() {
        $this->defaults = array(
        self::SCOPE => Scope::SINGLETON,
        self::POST_CONSTRUCT => null,
        self::PRE_DESTROY => null,
        self::INJECT => array(),
        self::IMPLEMENTEDBY => array()
        );
    }

    /**
     * (non-PHPdoc)
     * @see ArrayObject::offsetSet()
     */
    public function offsetSet($offset, $value) {
        if (is_null($offset) || !is_array($value)) {
            throw new \InvalidArgumentException;
        }
        $value = array_merge($this->defaults, $value);
        $this->container[$offset] = $value;
    }

    /**
     * (non-PHPdoc)
     * @see ArrayObject::offsetExists()
     */
    public function offsetExists($offset) {
        return isset($this->container[$offset]);
    }

    /**
     * (non-PHPdoc)
     * @see ArrayObject::offsetUnset()
     */
    public function offsetUnset($offset) {
        unset($this->container[$offset]);
    }

    /**
     * (non-PHPdoc)
     * @see ArrayObject::offsetGet()
     */
    public function offsetGet($offset) {
        return isset($this->container[$offset]) ? $this->container[$offset] : null;
    }

    public function __toString()
    {
        return var_export($this->container, true);
    }

    public function getIterator() {
        return new \ArrayIterator($this->container);
    }
}