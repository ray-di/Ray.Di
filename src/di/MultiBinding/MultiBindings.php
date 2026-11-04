<?php

declare(strict_types=1);

namespace Ray\Di\MultiBinding;

use ArrayObject;
use Ray\Di\Types;

use function is_int;

/**
 * @psalm-import-type LazyBindingList from Types
 * @psalm-capabilities read-props|write-this-props
 * @extends ArrayObject<string, LazyBindingList>
 */
final class MultiBindings extends ArrayObject
{
    public function merge(self $multiBindings): void
    {
        foreach ($multiBindings->getArrayCopy() as $interface => $lazies) {
            /** @var LazyBindingList $existing */
            $existing = $this->offsetExists($interface) ? $this[$interface] : [];
            /** @psalm-suppress ImpureMethodCall -- storage is always the default array */
            $this->offsetSet($interface, $this->mergeEntries($existing, $lazies));
        }
    }

    /**
     * An existing string key keeps its first entry, matching the += winner of
     * the container merge; integer-keyed (unnamed) entries append
     *
     * @param LazyBindingList $existing
     * @param LazyBindingList $lazies
     *
     * @return LazyBindingList
     */
    private function mergeEntries(array $existing, array $lazies): array
    {
        foreach ($lazies as $key => $lazy) {
            if (is_int($key)) {
                $existing[] = $lazy;
                continue;
            }

            $existing[$key] ??= $lazy;
        }

        return $existing;
    }
}
