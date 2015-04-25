<?php
/**
 * This file is part of the Ray package.
 *
 * @license http://opensource.org/licenses/bsd-license.php BSD
 */
namespace Ray\Di;

use Ray\Di\Exception\Compile;

final class DiCompiler implements InjectorInterface
{
    /**
     * @var string
     */
    private $classDir;

    /**
     * @var Container
     */
    private $container;

    /**
     * @var DependencyCompiler
     */
    private $dependencyCompiler;

    /**
     * @var Injector
     */
    private $injector;

    /**
     * @var AbstractModule
     */
    private $module;

    /**
     * @param AbstractModule $module
     * @param string         $classDir
     */
    public function __construct(AbstractModule $module = null, $classDir = null)
    {
        $this->classDir = $classDir ?: sys_get_temp_dir();
        $this->container =  $module ? $module->getContainer() : new Container;
        $this->injector = new Injector($module, $classDir);
        $this->dependencyCompiler = new DependencyCompiler($this->container);
        $this->module = $module;
    }

    /**
     * @param string $interface
     * @param string $name
     *
     * @return mixed
     */
    public function getInstance($interface, $name = Name::ANY)
    {
        $instance = $this->injector->getInstance($interface, $name);
        $this->compile();

        return $instance;
    }

    public function compile()
    {
        $container = $this->container->getContainer();
        foreach ($container as $dependencyIndex => $dependency) {
            $file = sprintf('%s/%s.php', $this->classDir, str_replace('\\', '_', $dependencyIndex));
            if (! file_exists($file)) {
                $code = $this->dependencyCompiler->compile($dependency);
                file_put_contents($file, (string) $code, LOCK_EX);
            }
        }
        $file = $this->classDir . '/module.php';
        $module = sprintf('<?php return unserialize(\'%s\');', serialize($this->module));
        file_put_contents($file, $module);
    }
}
