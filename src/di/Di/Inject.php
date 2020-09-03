<?php

declare(strict_types=1);

namespace Ray\Di\Di;

/**
 * Annotates your class methods into which the Injector should inject values
 *
 * @Annotation
 * @Target("METHOD")
 */
final class Inject implements InjectInterface
{
    /**
     * If true, and the appropriate binding is not found, the Injector will skip injection of this method or field rather than produce an error.
     *
     * @var bool
     */
    public $optional = false;

    /**
     * {@inheritdoc}
     */
    public function isOptional() : bool
    {
        return $this->optional;
    }
}
