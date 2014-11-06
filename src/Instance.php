<?php
/**
 * This file is part of the Ray package.
 *
 * @license http://opensource.org/licenses/bsd-license.php BSD
 */
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

    public function register(array &$container, Bind $bind)
    {
        $index = (string) $bind;
        if (! isset($container[$index])) {
            $container[$index] = $bind->getBound();

            return;
        }
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
     * @codeCoverageIgnore
     */
    public function setScope($scope)
    {
    }
}
