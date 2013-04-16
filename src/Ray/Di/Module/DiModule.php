<?php
/**
 * This file is part of the Ray package.
 *
 * @package Ray.Di
 * @license http://opensource.org/licenses/bsd-license.php BSD
 */
namespace Ray\Di\Module;

use Doctrine\Common\Annotations\AnnotationReader;
use Doctrine\Common\Annotations\CachedReader;
use LogicException;
use Ray\Di\AbstractModule;
use Ray\Di\Exception;

/**
 * Dependency Injector Module
 *
 * @package Ray.Di
 */
class DiModule extends AbstractModule
{
    protected function configure()
    {
        $this->bind('Aura\Di\ConfigInterface')->to('Aura\Di\Config');
        $this->bind('Aura\ForgeInterface')->to('Aura\ForgeI');
        $this->bind('Aura\Di\ContainerInterface')->to('Ray\Di\Container');
        $this->bind('Aura\Di\ForgeInterface')->to('Ray\Di\Forge');
        $this->bind('Ray\Di\InjectorInterface')->to('Ray\Di\Injector');
        $this->bind('Ray\Di\AbstractModule')->toInstance($this);
    }
}
