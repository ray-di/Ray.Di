<?php
/**
 * This file is part of the Ray package.
 *
 * @license http://opensource.org/licenses/bsd-license.php BSD
 */
namespace Ray\Di;

class AopClassLoader
{
    public function register($classDir)
    {
        spl_autoload_register(
            function ($class) use ($classDir) {
                $file = $classDir . DIRECTORY_SEPARATOR . $class . '.php';
                if (file_exists($file)) {
                    // @codeCoverageIgnoreStart
                    include $file;
                    // @@codeCoverageIgnoreEnd
                }
            }
        );
    }
}
