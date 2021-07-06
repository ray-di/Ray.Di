<?php

declare(strict_types=1);

namespace Ray\Di;

use Doctrine\Common\Annotations\AnnotationException;
use Ray\Aop\Compiler;
use Ray\Di\Annotation\ScriptDir;
use Ray\Di\Exception\Unbound;
use Ray\Di\Exception\Untargeted;

use function assert;
use function file_exists;
use function file_put_contents;
use function is_dir;
use function spl_autoload_register;
use function sprintf;
use function str_replace;
use function sys_get_temp_dir;

class Injector implements InjectorInterface
{
    /** @var string */
    private $classDir;

    /** @var Container */
    private $container;

    /**
     * @param AbstractModule $module Binding module
     * @param string         $tmpDir Temp directory for generated class
     */
    public function __construct(?AbstractModule $module = null, string $tmpDir = '')
    {
        $module = $module ?? new NullModule();
        $module->install(new AssistedModule());
        $this->classDir = is_dir($tmpDir) ? $tmpDir : sys_get_temp_dir();
        $this->container = $module->getContainer();
        $this->container->map(function (DependencyInterface $dependency) {
            if ($dependency instanceof NullObjectDependency) {
                return $dependency->toNull($this->classDir);
            }

            return $dependency;
        });
        $this->container->weaveAspects(new Compiler($this->classDir));

        // builtin injection
        (new Bind($this->container, InjectorInterface::class))->toInstance($this);
        (new Bind($this->container, ''))->annotatedWith(ScriptDir::class)->toInstance($this->classDir);
    }

    /**
     * Wakeup
     */
    public function __wakeup()
    {
        spl_autoload_register(
            function (string $class): void {
                $file = sprintf('%s/%s.php', $this->classDir, str_replace('\\', '_', $class));
                if (file_exists($file)) {
                    include $file; //@codeCoverageIgnore
                }
            }
        );
    }

    /**
     * Return instance
     *
     * @param class-string|string $interface
     * @param string              $name
     *
     * @return mixed instance
     */
    public function getInstance($interface, $name = Name::ANY)
    {
        try {
            /** @psalm-suppress MixedAssignment */
            $instance = $this->container->getInstance($interface, $name);
        } catch (Untargeted $e) {
            $this->bind($interface);
            /** @psalm-suppress MixedAssignment */
            $instance = $this->getInstance($interface, $name);
        } catch (Unbound $e) {
            $bindings =  (new ModuleString())($this->container, $this->container->getPointcuts());
            $logFile = sprintf('%s/module.log', $this->classDir);
            file_put_contents($logFile, $bindings);
            $message = sprintf("%s\nSee the binding log: %s", $e->getMessage(), $logFile);

            throw new Unbound($message);
        }

        return $instance;
    }

    /**
     * @phpstan-param class-string|string $class
     *
     * @throws AnnotationException
     */
    private function bind(string $class): void
    {
        new Bind($this->container, $class);
        $bound = $this->container->getContainer()[$class . '-' . Name::ANY];
        assert($bound instanceof Dependency);
        $this->container->weaveAspect(new Compiler($this->classDir), $bound)->getInstance($class, Name::ANY);
    }
}
