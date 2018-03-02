<?php

declare(strict_types=1);
/**
 * This file is part of the Ray.Di package.
 *
 * @license http://opensource.org/licenses/MIT MIT
 */
namespace Ray\Di;

/**
 * Interface for context of object provider
 */
interface SetContextInterface
{
    /**
     * Set provider context
     *
     * @return mixed
     */
    public function setContext($context);
}
