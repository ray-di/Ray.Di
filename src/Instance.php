<?php
/**
 * This file is part of the Ray.Di package.
 *
 * @license http://opensource.org/licenses/MIT MIT
 */
namespace Ray\Di;

final class Instance implements DependencyInterface
{
    /**
     * @var mixed
     */
    public $value;

    public function __construct($value)
    {
        $this->value = $value;
    }

    /**
     * {@inheritdoc}
     */
    public function register(array &$container, Bind $bind)
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
    public function setScope($scope)
    {
    }

    public function getDebugInfo()
    {
        return sprintf(
            '<Instance %s (of type %s)>',
            (string)$this->value,
             is_object($this->value) ? get_class($this->value) : gettype($this->value)
        );
    }
}
