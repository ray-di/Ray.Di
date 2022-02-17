<?php

declare(strict_types=1);

namespace Ray\Di;

use function array_shift;

final class ModuleMerger
{
    /**
     * @param array<AbstractModule> $modules
     */
    public function __invoke(array $modules): ?AbstractModule
    {
        $module = array_shift($modules);
        foreach ($modules as $module) {
            $module->install($module);
        }

        return $module;
    }
}
