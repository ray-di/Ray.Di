<?php
/**
 * This file is part of the Ray package.
 *
 * @license http://opensource.org/licenses/bsd-license.php BSD
 */
namespace Ray\Di;

final class DependencyReference implements ProviderInterface, \Serializable
{
    /**
     * @var CompilationLogger
     */
    private $logger;

    /**
     * @var string
     */
    private $refId;

    /**
     * @var object
     */
    private $instance;

    /**
     * Dependency type (class name)
     *
     * @var string
     */
    private $type;

    /**
     * @param string            $refId
     * @param CompilationLogger $logger
     * @param string            $type
     */
    public function __construct($refId, CompilationLogger $logger, $type)
    {
        $this->refId = $refId;
        $this->logger = $logger;
        $this->type = $type;
    }

    public function get()
    {
        if ($this->instance !== null) {
            return $this->instance;
        }
        $this->instance = $this->logger->newInstance($this->refId);

        return $this->instance;
    }

    public function serialize()
    {
        $serialized = serialize(
            [
                $this->logger,
                $this->refId,
                $this->type
            ]
        );

        return $serialized;
    }

    public function unserialize($serialized)
    {
        list(
            $this->logger,
            $this->refId,
            $this->type
        ) = unserialize($serialized);
    }

    public function __toString()
    {
        return '#' . $this->refId;
    }
}
