<?php
/**
 * Ray
 *
 * @package Ray.Di
 * @license http://opensource.org/licenses/bsd-license.php BSD
 */
namespace Ray\Di;

/**
 *
 * Retains target class inject definition.
 *
 * @package Ray.Di
 * @author  Akihito Koriyama <akihito.koriyama@gmail.com>
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
     * Aspect annotation
     *
     * @var string
     */
    const ASPECT ='Aspect';

    /**
     * User defined interceptor annotation
     *
     * @var string
     */
    const USER = 'user';
    const OPTIONS = 'options';
    const BINDING = 'binding';

    /**
     * Constructor
     */
    public function __construct(
        $defaults = [
            self::SCOPE => Scope::PROTOTYPE,
            self::POST_CONSTRUCT => null,
            self::PRE_DESTROY => null,
            self::INJECT => [],
            self::IMPLEMENTEDBY => [],
            self::USER => []
        ]
    )
    {
        parent::__construct($defaults);
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