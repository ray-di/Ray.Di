<?php
/**
 * This file is part of the Ray package.
 *
 * @license http://opensource.org/licenses/bsd-license.php BSD
 */
namespace Ray\Di;

interface InjectInterface
{
    /**
     * Inject dependencies into dependent objects
     *
     * @param Container $container
     *
     * @return mixed
     */
    public function inject(Container $container);
}