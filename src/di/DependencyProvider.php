<?php

declare(strict_types=1);

namespace Ray\Di;

use function assert;
use function sprintf;

final class DependencyProvider implements DependencyInterface, AcceptInterface
{
    private bool $isSingleton = false;
    private bool $isInstantiated = false;

    /** @var ?mixed */
    private $instance;

    public function __construct(
        /**
         * Provider dependency
         */
        private readonly Dependency $dependency,
        public readonly string $context
    ) {
    }

    /**
     * @return list<string>
     */
    public function __sleep()
    {
        return ['context', 'dependency', 'isSingleton'];
    }

    public function __toString(): string
    {
        return sprintf(
            '(provider) %s',
            (string) $this->dependency
        );
    }

    /**
     * {@inheritdoc}
     */
    public function register(array &$container, Bind $bind): void
    {
        $container[(string) $bind] = $bind->getBound();
    }

    /**
     * {@inheritdoc}
     */
    public function inject(Container $container)
    {
        if ($this->isSingleton && $this->isInstantiated) {
            return $this->instance;
        }

        $provider = $this->dependency->inject($container);
        assert($provider instanceof ProviderInterface);
        if ($provider instanceof SetContextInterface) {
            $this->setContext($provider);
        }

        $this->instance = $provider->get();
        $this->isInstantiated = true;

        return $this->instance;
    }

    /**
     * {@inheritdoc}
     */
    public function setScope($scope): void
    {
        if ($scope === Scope::SINGLETON) {
            $this->isSingleton = true;
        }
    }

    public function setContext(SetContextInterface $provider): void
    {
        $provider->setContext($this->context);
    }

    public function isSingleton(): bool
    {
        return $this->isSingleton;
    }

    /** @inheritDoc */
    public function accept(VisitorInterface $visitor)
    {
        return $visitor->visitProvider(
            $this->dependency,
            $this->context,
            $this->isSingleton
        );
    }
}
