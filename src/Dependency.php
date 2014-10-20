<?php
/**
 * This file is part of the Ray package.
 *
 * @license http://opensource.org/licenses/bsd-license.php BSD
 */
namespace Ray\Di;

final class Dependency implements InjectInterface
{
    /**
     * @var NewInstance
     */
    private $newInstance;

    /**
     * @var string
     */
    private $postConstruct;

    /**
     * @var bool
     */
    private $isSingleton = false;

    /**
     * @var mixed
     */
    private $instance;

    /**
     * @param NewInstance       $newInstance
     * @param \ReflectionMethod $postConstruct
     */
    public function __construct(NewInstance $newInstance, \ReflectionMethod $postConstruct = null)
    {
        $this->newInstance = $newInstance;
        if ($postConstruct) {
            $this->postConstruct = $postConstruct->name;
        }
    }

    /**
     * @param Container $container
     *
     * @return mixed|object
     */
    public function inject(Container $container)
    {
        // singleton ?
        if ($this->isSingleton === true && $this->instance) {

            return $this->instance;
        }

        // create dependency injected instance
        $this->instance = $this->newInstance->__invoke($container);

        // @PostConstruct
        if ($this->postConstruct) {
            $this->instance->{$this->postConstruct}();
        }

        return $this->instance;
    }

    /**
     * @param string $scope
     */
    public function setScope($scope)
    {
        if ($scope === Scope::SINGLETON) {
            $this->isSingleton = true;
        }
    }

    public function __sleep()
    {
        return ['newInstance', 'postConstruct', 'isSingleton'];
    }
}
