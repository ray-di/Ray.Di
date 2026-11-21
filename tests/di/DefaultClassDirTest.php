<?php

declare(strict_types=1);

namespace Ray\Di;

use PHPUnit\Framework\TestCase;
use Ray\Di\Exception\DirectoryNotPrivate;
use ReflectionClass;

use function chmod;
use function dirname;
use function fileowner;
use function fileperms;
use function glob;
use function mkdir;
use function realpath;
use function rename;
use function rmdir;
use function sprintf;
use function symlink;
use function sys_get_temp_dir;
use function tempnam;
use function uniqid;
use function unlink;

use const DIRECTORY_SEPARATOR;

/**
 * Generated classes are `require`d, so they must never be read from a directory another local user can write.
 */
class DefaultClassDirTest extends TestCase
{
    private string $privateDir;

    protected function setUp(): void
    {
        if (DIRECTORY_SEPARATOR === '\\') {
            $this->markTestSkipped('The Windows temp directory is per-user and has no POSIX owner/mode.');
        }

        $probe = (string) tempnam(sys_get_temp_dir(), 'di');
        $this->privateDir = sprintf('%s/ray-di-%d', sys_get_temp_dir(), (int) fileowner($probe));
        unlink($probe);
    }

    public function testProxyIsLoadedFromPrivateDir(): void
    {
        $instance = (new Injector(new FakeAopModule()))->getInstance(FakeAopInterface::class);

        $proxyFile = (string) (new ReflectionClass($instance))->getFileName();
        $this->assertSame(realpath($this->privateDir), dirname((string) realpath($proxyFile)));
        $this->assertSame(0, (int) fileperms($this->privateDir) & 0022);
    }

    public function testMissingTmpDirFallsBackToPrivateDir(): void
    {
        $instance = (new Injector(new FakeAopModule(), __DIR__ . '/tmp/no-such-dir'))->getInstance(FakeAopInterface::class);

        $proxyFile = (string) (new ReflectionClass($instance))->getFileName();
        $this->assertSame(realpath($this->privateDir), dirname((string) realpath($proxyFile)));
    }

    public function testGrapherWritesNullObjectIntoItsClassDir(): void
    {
        $classDir = __DIR__ . '/tmp/' . uniqid('grapher');
        mkdir($classDir);

        new Grapher(new class extends AbstractModule {
            protected function configure()
            {
                $this->bind(FakeTyreInterface::class)->toNull();
            }
        }, $classDir);

        $this->assertCount(1, (array) glob($classDir . '/*Null.php'));
    }

    public function testDirWritableByOthersIsRejected(): void
    {
        new Injector();
        chmod($this->privateDir, 0777);

        try {
            $this->expectException(DirectoryNotPrivate::class);
            new Injector();
        } finally {
            chmod($this->privateDir, 0700);
        }
    }

    public function testSymlinkedDirIsRejected(): void
    {
        new Injector();
        $aside = $this->privateDir . '.aside';
        $ownDir = __DIR__ . '/tmp/' . uniqid('own');
        mkdir($ownDir, 0700);
        rename($this->privateDir, $aside);
        symlink($ownDir, $this->privateDir);

        try {
            $this->expectException(DirectoryNotPrivate::class);
            new Injector();
        } finally {
            unlink($this->privateDir);
            rename($aside, $this->privateDir);
            rmdir($ownDir);
        }
    }
}
