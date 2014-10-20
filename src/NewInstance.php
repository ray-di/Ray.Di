<?php
/**
 * This file is part of the Ray package.
 *
 * @license http://opensource.org/licenses/bsd-license.php BSD
 */
namespace Ray\Di;

final class NewInstance implements \Serializable
{
    /**
     * @var \ReflectionClass
     */
    private $class;

    /**
     * @var SetterMethods
     */
    private $setterMethods;

    /**
     * @var Parameters
     */
    private $parameters;

    /**
     * @param \ReflectionClass $class
     * @param SetterMethods    $setterMethods
     * @param Name             $constructorName
     */
    public function __construct(
        \ReflectionClass $class,
        SetterMethods $setterMethods = null,
        Name $constructorName = null
    ) {
        $constructorName = $constructorName ?: new Name(Name::ANY);
        $this->class = $class;
        $constructor = $class->getConstructor();
        if ($constructor) {
            $this->parameters = new Parameters($constructor, $constructorName);
        }
        $this->setterMethods = $setterMethods;
    }

    /**
     * @param Container $container
     *
     * @return object
     */
    public function __invoke(Container $container)
    {
        // constructor injection
        $instance = $this->parameters ? $this->class->newInstanceArgs($this->parameters->get($container)) : new $this->class->name;

        // setter injection
        if ($this->setterMethods) {
            $this->setterMethods->__invoke($instance, $container);
        }

        if ($instance instanceof ProviderInterface) {
            $instance = $instance->get();
        }
        return $instance;
    }

    public function serialize()
    {
        return serialize([$this->class->name, $this->setterMethods, $this->parameters]);
    }

    public function unserialize($serialized)
    {
        list($class, $this->setterMethods, $this->parameters) = unserialize($serialized);
        $this->class = new \ReflectionClass($class);
    }
}
