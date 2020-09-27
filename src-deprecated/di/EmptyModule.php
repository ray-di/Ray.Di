<?php

declare(strict_types=1);

namespace Ray\Di;

/**
 * @deprecated User NullModule instead
 */
class EmptyModule extends AbstractModule
{
    /**
     * {@inheritdoc}
     */
    protected function configure() : void
    {
    }
}
