<?php

declare(strict_types=1);

namespace Ray\Di\Di;

use Attribute;
use Doctrine\Common\Annotations\Annotation\NamedArgumentConstructor;

/**
 * Annotates named things
 *
 * @Annotation
 * @Target("METHOD")
 * @NamedArgumentConstructor
 */
#[Attribute(Attribute::TARGET_PARAMETER | Attribute::TARGET_METHOD)]
final class Named
{
    /** @var string */
    public $value = '';

    public function __construct(string $value)
    {
        $this->value = $value;
    }
}
