<?php
/**
 * This file is part of the Ray package.
 *
 * @license http://opensource.org/licenses/bsd-license.php BSD
 */
namespace Ray\Di;

use Ray\Di\Exception\NotCompiled;

class ScriptInjector implements InjectorInterface
{
    /**
     * @var string
     */
    private $scriptDir;

    /**
     * @param string $scriptDir generated instance script folder path
     */
    public function __construct($scriptDir)
    {
        $this->scriptDir = $scriptDir;
    }

    /**
     * {@inheritdoc}
     */
    public function getInstance($interface, $name = Name::ANY)
    {
        return $this->getScriptInstance($interface . '-' . $name);
    }

    /**
     * @param string $dependencyIndex
     *
     * @return mixed
     */
    private function getScriptInstance($dependencyIndex)
    {
        $file = sprintf('%s/%s.php', $this->scriptDir, str_replace('\\', '_', $dependencyIndex));
        if (! file_exists($file)) {
            throw new NotCompiled($file);
        }
        $prototype = function ($dependencyIndex) {
            return $this->getScriptInstance($dependencyIndex);
        };
        $singleton = function ($dependencyIndex) {
            static $singletons;

            if (isset($singletons[$dependencyIndex])) {
                return $singletons[$dependencyIndex];
            }
            $instance = $this->getScriptInstance($dependencyIndex);
            $singletons[$dependencyIndex] = $instance;

            return $instance;
        };
        $instance = require $file;

        return $instance;
    }
}
