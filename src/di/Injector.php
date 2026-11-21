<?php

declare(strict_types=1);

namespace Ray\Di;

use Ray\Aop\Compiler;
use Ray\Di\Exception\DirectoryNotPrivate;
use Ray\Di\Exception\DirectoryNotWritable;
use Ray\Di\Exception\Unbound;
use Ray\Di\Exception\Untargeted;

use function assert;
use function clearstatcache;
use function file_exists;
use function fileowner;
use function is_dir;
use function is_writable;
use function lstat;
use function mkdir;
use function spl_autoload_register;
use function sprintf;
use function str_replace;
use function sys_get_temp_dir;
use function tempnam;
use function trigger_error;
use function unlink;

use const DIRECTORY_SEPARATOR;
use const E_USER_DEPRECATED;

/**
 * @psalm-import-type BindableInterface from Types
 * @psalm-import-type ModuleList from Types
 * @psalm-import-type ScriptDir from Types
 */
final class Injector implements InjectorInterface
{
    private const WRITABLE_BY_GROUP_OR_OTHERS = 0022;
    private const FILE_TYPE = 0170000;
    private const DIRECTORY = 0040000;

    /** @var ScriptDir */
    private readonly string $classDir;
    private readonly Container $container;

    /**
     * @param AbstractModule|ModuleList|null $module Module(s)
     * @param string                         $tmpDir Directory for generated classes; must not be writable by other users
     */
    public function __construct($module = null, string $tmpDir = '')
    {
        $ownsDir = is_dir($tmpDir);
        /** @var ScriptDir $classDir */
        $classDir = $ownsDir ? $tmpDir : $this->privateTmpDir();
        if (! is_writable($classDir)) {
            throw new DirectoryNotWritable($classDir); // @codeCoverageIgnore
        }

        $this->classDir = $classDir;
        $this->container = (new ContainerFactory())($module, $this->classDir);
        // Bind injector (built-in bindings)
        (new Bind($this->container, InjectorInterface::class, self::class))->toInstance($this);
        $this->container->sort();
        if ($ownsDir) {
            (new BindingsMarkdown())($this->container, $classDir);
        }
    }

    /**
     * Per-user directory under the system temp directory, whose sticky bit keeps others from replacing it
     */
    private function privateTmpDir(): string
    {
        $tmpDir = sys_get_temp_dir();
        // @codeCoverageIgnoreStart
        if (DIRECTORY_SEPARATOR === '\\') {
            return $tmpDir; // no POSIX owner/mode
        }

        // @codeCoverageIgnoreEnd

        $uid = $this->uid($tmpDir);
        $dir = sprintf('%s/ray-di-%d', $tmpDir, $uid);
        @mkdir($dir, 0700);
        clearstatcache(true, $dir);
        // lstat does not follow a symlink
        $stat = @lstat($dir);
        $isPrivate = $stat !== false
            && ($stat['mode'] & self::FILE_TYPE) === self::DIRECTORY
            && $stat['uid'] === $uid
            && ($stat['mode'] & self::WRITABLE_BY_GROUP_OR_OTHERS) === 0;
        if (! $isPrivate) {
            throw new DirectoryNotPrivate($dir);
        }

        return $dir;
    }

    /**
     * uid of the current user without ext-posix
     */
    private function uid(string $tmpDir): int
    {
        $probe = is_writable($tmpDir) ? tempnam($tmpDir, 'di') : false;
        $uid = $probe === false ? false : fileowner($probe);
        // @codeCoverageIgnoreStart
        if ($probe === false || $uid === false) {
            throw new DirectoryNotWritable($tmpDir);
        }

        // @codeCoverageIgnoreEnd

        @unlink($probe);

        return $uid;
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
     * {@inheritDoc}
     */
    public function getInstance($interface, $name = Name::ANY)
    {
        try {
            /** @psalm-suppress MixedAssignment */
            $instance = $this->container->getInstance($interface, $name);
        } catch (Untargeted $untargeted) {
            // Just-in-time binding registers the class under Name::ANY only, so a
            // named request can never be satisfied by it and would retry forever.
            if ($name !== Name::ANY) {
                throw new Unbound(sprintf("'%s-%s'", $interface, $name), 0, $untargeted);
            }

            trigger_error(
                sprintf(
                    'Just-in-time binding of unbound concrete class "%s" is deprecated. Bind it explicitly: JIT resolution is unavailable under CompiledInjector and is not recorded in bindings.md.',
                    $interface,
                ),
                E_USER_DEPRECATED,
            );

            /**
             * @psalm-var class-string $interface
             * @psalm-suppress MixedAssignment
             */
            $instance = $this->bind($interface);
        }

        /** @psalm-suppress MixedReturnStatement */
        return $instance;
    }

    /**
     * @param BindableInterface $class
     *
     * @return mixed
     */
    private function bind(string $class)
    {
        new Bind($this->container, $class, self::class);
        $bound = $this->container->getContainer()[$class . '-' . Name::ANY];
        assert($bound instanceof Dependency);

        /** @psalm-suppress InvalidArgument */
        return $this->container->weaveAspect(new Compiler($this->classDir), $bound)->getInstance($class);
    }
}
