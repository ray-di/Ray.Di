<?php
/**
 * This file is part of the Ray package.
 *
 * @license http://opensource.org/licenses/bsd-license.php BSD
 */
namespace Ray\Di;

use Aura\Di\ConfigInterface;

/**
 * Interface for di compile logger
 */
interface CompileLoggerInterface extends LoggerInterface
{
    /**
     * @param ConfigInterface $config
     *
     * @return self
     */
    public function setConfig(ConfigInterface $config);

    /**
     * @param string $ref
     *
     * @return mixed
     * @throws \LogicException
     */
    public function newInstance($ref);

    /**
     * @param $object
     *
     * @return string
     */
    public function getObjectHash($object);
}
