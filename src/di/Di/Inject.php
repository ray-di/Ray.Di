<?php

declare(strict_types=1);

namespace Ray\Di\Di;

use Attribute;

#[Attribute(Attribute::TARGET_METHOD)]
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
     * @param array{optional?: bool} $values
     */
    public function __construct(array $values = [], bool $optional = false)
    {
        $this->optional = $values['optional'] ?? $optional;
    }

    /**
     * {@inheritdoc}
     */
    public function isOptional(): bool
    {
        return $this->optional;
    }
}
