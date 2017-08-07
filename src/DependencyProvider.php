<?php

declare(strict_types=1);
/**
 * This file is part of the Ray.Di package.
 *
 * @license http://opensource.org/licenses/MIT MIT
 */
namespace Ray\Di;

final class DependencyProvider implements DependencyInterface
{
    /**
     * @var string
     */
    public $context;

    /**
     * Provider dependency
     *
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

    public function __construct(Dependency $dependency, string $context = null)
    {
        $this->dependency = $dependency;
        $this->context = $context;
    }

    public function __sleep()
    {
        return ['dependency', 'isSingleton'];
    }

    /**
     * {@inheritdoc}
     */
    public function register(array &$container, Bind $bind) : void
    {
        $container[(string) $bind] = $bind->getBound();
    }

    /**
     * {@inheritdoc}
     */
    public function inject(Container $container)
    {
        if ($this->isSingleton && $this->instance) {
            return $this->instance;
        }
        $provider = $this->dependency->inject($container);
        if ($provider instanceof SetContextInterface) {
            $this->setContext($provider);
        }
        $this->instance = $provider->get();

        return $this->instance;
    }

    /**
     * {@inheritdoc}
     */
    public function setScope($scope) : void
    {
        if ($scope === Scope::SINGLETON) {
            $this->isSingleton = true;
        }
    }

    public function setContext(SetContextInterface $provider)
    {
        $provider->setContext($this->context);
    }
}
