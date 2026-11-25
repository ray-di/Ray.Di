<?php

declare(strict_types=1);

namespace Ray\Di;

use Ray\Di\Exception\EnvNotFound;

use function getenv;

/**
 * Provides the environment variable named by the binding context, read at get() time
 * so compiled containers hold the key, not the value.
 *
 * @implements ProviderInterface<string>
 */
final class EnvProvider implements ProviderInterface, SetContextInterface
{
    private string $key = '';

    /** @param string $context Environment variable name */
    public function setContext($context): void
    {
        $this->key = $context;
    }

    /** @throws EnvNotFound */
    public function get(): string
    {
        $value = getenv($this->key);
        if ($value === false) {
            throw new EnvNotFound($this->key);
        }

        return $value;
    }
}
