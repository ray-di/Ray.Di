<?php
/**
 * This file is part of the Ray package.
 *
 * @license http://opensource.org/licenses/bsd-license.php BSD
 */
namespace Ray\Di;

/**
 * Defines the interface for dependency injector.
 */
interface InjectorInterface extends InstanceInterface
{
    /**
     * Return container
     *
     * @return Container
     */
    public function getContainer();

    /**
     * Return module
     *
     * @return AbstractModule
     */
    public function getModule();

    /**
     * Set module
     *
     * @param AbstractModule $module
     *
     * @return self
     */
    public function setModule(AbstractModule $module);

    /**
     * Return injection logger
     *
     * @return LoggerInterface
     */
    public function getLogger();

    /**
     * Set logger
     *
     * @return self
     */
    public function setLogger(LoggerInterface $logger);
}
