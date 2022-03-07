<?php

declare(strict_types=1);

namespace Ray\Di;

use function array_shift;

final class ModuleMerger
{
    /**
     * @param non-empty-array<AbstractModule> $modules
     */
    public function __invoke(array $modules): AbstractModule
    {
        $baseModule = array_shift($modules);
        foreach ($modules as $module) {
            $baseModule->install($module);
        }

        return $baseModule;
    }
}
