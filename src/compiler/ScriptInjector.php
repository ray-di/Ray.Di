<?php

declare(strict_types=1);

namespace Ray\Compiler;

use Ray\Compiler\Exception\Unbound;
use Ray\Di\AbstractModule;
use Ray\Di\Dependency;
use Ray\Di\DependencyInterface;
use Ray\Di\InjectorInterface;
use Ray\Di\Name;
use Ray\Di\NullModule;

final class ScriptInjector implements InjectorInterface
{
    const MODULE = '/_module.txt';

    const AOP = '/_aop.txt';

    const INSTANCE = '%s/%s.php';

    const QUALIFIER = '%s/qualifer/%s-%s-%s';

    /**
     * @var string
     */
    private $scriptDir;

    /**
     * Injection Point
     *
     * [$class, $method, $parameter]
     *
     * @var array{0: string, 1: string, 2: string}
     */
    private $ip = ['', '', ''];

    /**
     * Singleton instance container
     *
     * @var array<object>
     */
    private $singletons = [];

    /**
     * @var array<callable>
     */
    private $functions;

    /**
     * @var callable
     */
    private $lazyModule;

    /**
     * @var null|AbstractModule
     */
    private $module;

    /**
     * @var ?array<DependencyInterface>
     */
    private $container;

    /**
     * @var bool
     */
    private $isModuleLocked = false;

    /**
     * @var array<string>
     */
    private static $scriptDirs = [];

    /**
     * @param string   $scriptDir  generated instance script folder path
     * @param callable $lazyModule callable variable which return AbstractModule instance
     *
     * @psalm-suppress UnresolvableInclude
     */
    public function __construct($scriptDir, callable $lazyModule = null)
    {
        $this->scriptDir = $scriptDir;
        $this->lazyModule = $lazyModule ?: function () : NullModule {
            return new NullModule;
        };
        $this->registerLoader();
        $prototype =
            /**
             * @param array{0: string, 1: string, 2: string} $injectionPoint
             *
             * @return mixed
             */
            function (string $dependencyIndex, array $injectionPoint = ['', '', '']) {
                $this->ip = $injectionPoint; // @phpstan-ignore-line
                [$prototype, $singleton, $injection_point, $injector] = $this->functions;

                return require $this->getInstanceFile($dependencyIndex);
            };
        $singleton =
            /**
             * @param array{0: string, 1: string, 2: string} $injectionPoint
             *
             * @return mixed
             */
            function (string $dependencyIndex, $injectionPoint = ['', '', '']) {
                if (isset($this->singletons[$dependencyIndex])) {
                    return $this->singletons[$dependencyIndex];
                }
                $this->ip = $injectionPoint;
                [$prototype, $singleton, $injection_point, $injector] = $this->functions;

                $instance = require $this->getInstanceFile($dependencyIndex);
                $this->singletons[$dependencyIndex] = $instance;

                return $instance;
            };
        $injection_point = function () use ($scriptDir) : InjectionPoint {
            return new InjectionPoint(
                new \ReflectionParameter([$this->ip[0], $this->ip[1]], $this->ip[2]),
                $scriptDir
            );
        };
        $injector = function () : self {
            return $this;
        };
        $this->functions = [$prototype, $singleton, $injection_point, $injector];
    }

    public function __sleep()
    {
        $this->saveModule();

        return ['scriptDir', 'singletons'];
    }

    public function __wakeup()
    {
        $this->__construct(
            $this->scriptDir,
            function () {
                return $this->getModule();
            }
        );
    }

    /**
     * {@inheritdoc}
     */
    public function getInstance($interface, $name = Name::ANY)
    {
        $dependencyIndex = $interface . '-' . $name;
        if (isset($this->singletons[$dependencyIndex])) {
            return $this->singletons[$dependencyIndex];
        }
        [$prototype, $singleton, $injection_point, $injector] = $this->functions;
        /** @psalm-suppress UnresolvableInclude */
        $instance = require $this->getInstanceFile($dependencyIndex);
        /** @global bool $is_singleton */
        $isSingleton = (isset($is_singleton) && $is_singleton) ? true : false; // @phpstan-ignore-line
        if ($isSingleton) {
            $this->singletons[$dependencyIndex] = $instance;
        }

        return $instance;
    }

    public function clear() : void
    {
        $unlink = function (string $path) use (&$unlink) : void {
            foreach ((array) \glob(\rtrim($path, DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR . '*') as $f) {
                $file = (string) $f;
                \is_dir($file) ? $unlink($file) : \unlink($file);
                @\rmdir($file);
            }
        };
        $unlink($this->scriptDir);
    }

    public function isSingleton(string $dependencyIndex) : bool
    {
        if (! $this->container) {
            $module = $this->getModule();
            /* @var AbstractModule $module */
            $this->container = $module->getContainer()->getContainer();
        }

        if (! isset($this->container[$dependencyIndex])) {
            throw new Unbound($dependencyIndex);
        }
        $dependency = $this->container[$dependencyIndex];

        return $dependency instanceof Dependency ? (new PrivateProperty)($dependency, 'isSingleton') : false;
    }

    private function getModule() : AbstractModule
    {
        $modulePath = $this->scriptDir . self::MODULE;
        if (! file_exists($modulePath)) {
            return new NullModule;
        }
        $serialized = file_get_contents($modulePath);
        assert(! is_bool($serialized));
        $er = error_reporting(error_reporting() ^ E_NOTICE);
        $module = unserialize($serialized, ['allowed_classes' => true]);
        error_reporting($er);
        assert($module instanceof AbstractModule);

        return $module;
    }

    /**
     * Return compiled script file name
     */
    private function getInstanceFile(string $dependencyIndex) : string
    {
        $file = \sprintf(self::INSTANCE, $this->scriptDir, \str_replace('\\', '_', $dependencyIndex));
        if (\file_exists($file)) {
            return $file;
        }
        $this->compileOnDemand($dependencyIndex);
        assert(\file_exists($file));

        return $file;
    }

    private function saveModule() : void
    {
        if ($this->isModuleLocked || \file_exists($this->scriptDir . self::MODULE)) {
            return;
        }
        $this->isModuleLocked = true;
        $module = $this->module instanceof AbstractModule ? $this->module : ($this->lazyModule)();
        (new FilePutContents)($this->scriptDir . self::MODULE, \serialize($module));
    }

    private function registerLoader() : void
    {
        if (in_array($this->scriptDir, self::$scriptDirs, true)) {
            return;
        }
        if (self::$scriptDirs === []) {
            \spl_autoload_register(
                function (string $class) : void {
                    foreach (self::$scriptDirs as $scriptDir) {
                        $file = \sprintf('%s/%s.php', $scriptDir, \str_replace('\\', '_', $class));
                        if (\file_exists($file)) {
                            require $file; // @codeCoverageIgnore
                        }
                    }
                }
            );
        }
        self::$scriptDirs[] = $this->scriptDir;
    }

    private function compileOnDemand(string $dependencyIndex) : void
    {
        if (! $this->module instanceof AbstractModule) {
            $this->module = ($this->lazyModule)();
        }
        $isFirstCompile = ! \file_exists($this->scriptDir . self::AOP);
        if ($isFirstCompile) {
            (new DiCompiler(($this->lazyModule)(), $this->scriptDir))->savePointcuts($this->module->getContainer());
            $this->saveModule();
        }
        assert($this->module instanceof AbstractModule);
        (new OnDemandCompiler($this, $this->scriptDir, $this->module))($dependencyIndex);
    }
}
