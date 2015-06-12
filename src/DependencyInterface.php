<?php
/**
 * This file is part of the Ray.Di package.
 *
 * @license http://opensource.org/licenses/MIT MIT
 */
namespace Ray\Di;

interface DependencyInterface
{
    /**
     * Inject dependencies into dependent objects
     *
     * @param Container $container
     *
     * @return mixed
     */
    public function inject(Container $container);

    /**
     * Register dependency to container
     *
     * @param DependencyInterface[] $container
     * @param Bind                  $bind
     */
    public function register(array &$container, Bind $bind);

    /**
     * Set scope
     *
     * @param string $scope
     *
     * @return mixed
     */
    public function setScope($scope);

    /**
     * Get information about this dependency.
     * Used to list the chain of depdendencies leading to an error.
     * @return string
     */
    public function getDebugInfo();
}
