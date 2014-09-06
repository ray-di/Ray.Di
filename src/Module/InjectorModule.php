<?php
/**
 * This file is part of the Ray package.
 *
 * @license http://opensource.org/licenses/bsd-license.php BSD
 */
namespace Ray\Di\Module;

use Ray\Di\AbstractModule;
use Ray\Di\Scope;

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
        $this->bind('Ray\Di\AnnotationInterface')->to('Ray\Di\Annotation')->in(Scope::SINGLETON);
        $this->bind('Ray\Di\ConfigInterface')->to('Ray\Di\Config')->in(Scope::SINGLETON);
        $this->bind('Ray\Di\AnnotationInterface')->to('Ray\Di\Annotation')->in(Scope::SINGLETON);
        $this->bind('Ray\Aop\BindInterface')->toProvider(__NAMESPACE__ . '\Provider\BindProvider');
        $this->bind('Ray\Aop\CompilerInterface')->toProvider(__NAMESPACE__ . '\Provider\CompilerProvider');
        $this->bind('Doctrine\Common\Annotations\Reader')->to('Doctrine\Common\Annotations\AnnotationReader')->in(Scope::SINGLETON);
        $this->bind('Ray\Di\AbstractModule')->to(__CLASS__);
        $this->bind('Ray\Di\LoggerInterface')->to('Ray\Di\Logger')->in(Scope::SINGLETON);
    }
}
