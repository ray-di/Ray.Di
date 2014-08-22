<?php
/**
 * This file is part of the Ray package.
 *
 * @license http://opensource.org/licenses/bsd-license.php BSD
 */
namespace Ray\Di;

use Aura\Di\ConfigInterface;
use Aura\Di\Forge as AuraForge;
use Aura\Di\ForgeInterface;

/**
 * Creates objects using reflection and the specified configuration values.
 */
class Forge extends AuraForge implements ForgeInterface
{
    /**
     * @param ConfigInterface $config
     *
     * @Ray\Di\Di\Inject
     */
    public function __construct(ConfigInterface $config)
    {
        parent::__construct($config);
    }
}
