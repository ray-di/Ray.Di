<?php
/**
 * This file is part of the Ray package.
 *
 * @license http://opensource.org/licenses/bsd-license.php BSD
 */
namespace Ray\Di\Module;

use Ray\Di\AbstractModule;
use Ray\Di\Scope;
use Ray\Aop\Bind;

/**
 * Dependency Injector Module.
 */
class InjectorModule extends AbstractModule
{
    protected function configure()
    {
        $this->bind('Aura\Di\ConfigInterface')->to('Ray\Di\Config');
        $this->bind('Aura\Di\ContainerInterface')->to('Ray\Di\Container');
        $this->bind('Aura\Di\ForgeInterface')->to('Ray\Di\Forge');
        $this->bind('Ray\Di\InjectorInterface')->to('Ray\Di\Injector')->in(Scope::SINGLETON);
        $this->bind('Ray\Di\AnnotationInterface')->to('Ray\Di\Annotation');
        $this->bind('Ray\Aop\CompilerInterface')->toProvider(__NAMESPACE__ . '\Provider\CompilerProvider');
        $this->bind('Ray\Aop\BindInterface')->toInstance(new Bind);
        $this->bind('Ray\Di\AbstractModule')->toInstance($this);
        $this->bind('Doctrine\Common\Annotations\Reader')->to('Doctrine\Common\Annotations\AnnotationReader')->in(Scope::SINGLETON);
    }
}
