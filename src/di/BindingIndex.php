<?php

declare(strict_types=1);

namespace Ray\Di;

use function strrpos;
use function substr;

final class BindingIndex
{
    /**
     * @return array{string, string}
     */
    public static function parse(string $index): array
    {
        $pos = strrpos($index, '-');
        if ($pos === false) {
            return [$index, Name::ANY];
        }

        return [substr($index, 0, $pos), substr($index, $pos + 1)];
    }
}
