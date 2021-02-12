<?php

declare(strict_types=1);

namespace Ray\Di\Di;

use Attribute;
use Doctrine\Common\Annotations\NamedArgumentConstructorAnnotation;

/**
 * Annotates named things
 *
 * @Annotation
 * @Target("METHOD")
 */
#[Attribute(Attribute::TARGET_PARAMETER | Attribute::TARGET_METHOD)]
final class Named implements NamedArgumentConstructorAnnotation
{
    /** @var string */
    public $value = '';

    public function __construct(string $value)
    {
        $this->value = $value;
    }
}
