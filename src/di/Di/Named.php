<?php

declare(strict_types=1);

namespace Ray\Di\Di;

use Attribute;

use function is_array;
use function is_string;

/**
 * Annotates named things
 *
 * @Annotation
 * @Target("METHOD")
 */
#[Attribute(Attribute::TARGET_PARAMETER)]
final class Named
{
    /** @var string */
    public $value = '';

    /**
     * @param array{value: string}|string $value
     */
    public function __construct($value)
    {
        if (is_array($value) && isset($value['value'])) {
            // doctrine/annotations
            $this->value = $value['value'];
        }

        if (is_string($value)) {
            // php8 attribute
            $this->value = $value;
        }
    }
}
