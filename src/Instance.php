<?php

declare(strict_types=1);

namespace Ray\Di;

final class Instance implements DependencyInterface
{
    /**
     * @var mixed
     */
    public $value;

    /**
     * @param mixed $value
     */
    public function __construct($value)
    {
        $this->value = $value;
    }

    public function __toString()
    {
        if (is_scalar($this->value)) {
            return sprintf(
                '(%s) %s',
                gettype($this->value),
                (string) $this->value
            );
        }

        if (is_object($this->value)) {
            return '(object) ' . get_class($this->value);
        }

        return '(' . gettype($this->value) . ')';
    }

    /**
     * {@inheritdoc}
     */
    public function register(array &$container, Bind $bind) : void
    {
        $index = (string) $bind;
        $container[$index] = $bind->getBound();
    }

    /**
     * {@inheritdoc}
     */
    public function inject(Container $container)
    {
        unset($container);

        return $this->value;
    }

    /**
     * {@inheritdoc}
     *
     * @codeCoverageIgnore
     */
    public function setScope($scope) : void
    {
    }
}
