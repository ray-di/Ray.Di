<?php

declare(strict_types=1);

namespace Ray\Di;

final class DependencyProvider implements DependencyInterface
{
    /**
     * @var null|string
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

    public function __construct(Dependency $dependency, string $context)
    {
        $this->dependency = $dependency;
        $this->context = $context;
    }

    public function __sleep()
    {
        return ['dependency', 'isSingleton'];
    }

    public function __toString()
    {
        return sprintf(
            '(provider) %s',
            (string) $this->dependency
        );
    }

    /**
     * {@inheritdoc}
     */
    public function register(array &$container, Bind $bind)
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
    public function setScope($scope)
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
