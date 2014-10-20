<?php
/**
 * This file is part of the Ray package.
 *
 * @license http://opensource.org/licenses/bsd-license.php BSD
 */
namespace Ray\Di;

final class Provider implements InjectInterface
{
    /**
     * @var Dependency
     */
    private $dependency;

    /**
     * @var bool
     */
    private $isSingleton = false;

    /**
     * @var mixed
     */
    private $instance;

    /**
     * @param Dependency $dependency
     */
    public function __construct(Dependency $dependency)
    {
        $this->dependency = $dependency;
    }

    /**
     * @param Container $container
     *
     * @return mixed|object
     */
    public function inject(Container $container)
    {
        if ($this->isSingleton && $this->instance) {

            return $this->instance;
        }
        $this->instance = $this->dependency->inject($container);

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
}
