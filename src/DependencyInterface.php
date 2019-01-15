<?php

declare(strict_types=1);

namespace Ray\Di;

interface DependencyInterface
{
    /**
     * Inject dependencies into dependent objects
     */
    public function inject(Container $container);

    /**
     * Register dependency to container
     *
     * @param DependencyInterface[] $container
     */
    public function register(array &$container, Bind $bind);

    /**
     * Set scope
     *
     * @param string $scope
     */
    public function setScope($scope);
}
