<?php
/**
 * This file is part of the Ray package.
 *
 * @license http://opensource.org/licenses/bsd-license.php BSD
 */
namespace Ray\Di;

use Aura\Di\Container as AuraContainer;
use Aura\Di\ContainerInterface;
use Aura\Di\ForgeInterface;

/**
 * Dependency injection container.
 */
class Container extends AuraContainer implements ContainerInterface
{
    /**
     * @param ForgeInterface $forge
     *
     * @Ray\Di\Di\Inject
     */
    public function __construct(ForgeInterface $forge)
    {
        parent::__construct($forge);
    }
}
