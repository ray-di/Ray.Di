<?php
/**
 * Ray
 *
 * @package Ray.Di
 * @license  http://opensource.org/licenses/bsd-license.php BSD
 */
namespace Ray\Di;

use Aura\Di\Lazy,
Aura\Di\ContainerInterface;
use Ray\Aop\Bind,
Ray\Aop\Weaver;

/**
 * Dependency Injector using APC
 *
 * @package Ray.Di
 */
class ApcInjector extends Injector
{
    /**
     * Apc prefix key
     *
     * @var string
     */
    private $prefix;

    /**
     * Constructor
     *
     * @param array    $callableModules Callable[]
     * @param string   $key
     * @param Callable $container
     *
     * @throws Exception
     */
    public function __construct(array $callableModules,  $key = '', Callable $container = null)
    {
        $this->prefix = $key;
        $container = (is_null($container)) ?  new Container(new Forge(new Config(new Annotation(new Definition)))) : $container();
        foreach ($callableModules as $callableModule) {
            if (! is_callable($callableModule)) {
                throw new Exception('Module is not callable');
            }
            $module = (! isset($module)) ? $callableModule() : $module->install($callableModule());
        }
        parent::__construct($container,  $module);
    }

    /**
     * Get a service object using APC cache
     *
     * @return object
     */
    public function getInstance($class, array $params = null)
    {
        $this->prefix = md5($this->module);
        $object = apc_fetch($this->prefix . $class, $success);
        $object = $object ?: parent::getInstance($class, $params);
        if ($success !== true) {
            apc_store($this->prefix . $class, $object);
        }
        return $object;
    }
}
