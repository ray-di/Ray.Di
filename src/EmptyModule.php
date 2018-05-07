<?php

declare(strict_types=1);
/**
 * This file is part of the Ray.Di package.
 *
 * @license http://opensource.org/licenses/MIT MIT
 */
namespace Ray\Di;

/**
 * @deprecated User NullModule instead
 */
class EmptyModule extends AbstractModule
{
    /**
     * {@inheritdoc}
     */
    protected function configure()
    {
    }
}
