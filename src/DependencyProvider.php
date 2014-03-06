<?php
/**
 * This file is part of the Ray package.
 *
 * @license http://opensource.org/licenses/bsd-license.php BSD
 */
namespace Ray\Di;

final class DependencyProvider
{
    /**
     * @var ProviderInterface
     */
    public $provider;

    /**
     * @var object
     */
    public $instance;

    /**
     * @param ProviderInterface $provider
     * @param object            $instance
     */
    public function __construct(ProviderInterface $provider, $instance)
    {
        $this->provider = $provider;
        $this->instance = $instance;
    }
}
