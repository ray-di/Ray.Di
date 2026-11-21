<?php

declare(strict_types=1);

namespace Ray\Di;

use PHPUnit\Framework\TestCase;
use Ray\Di\Exception\DirectoryNotPrivate;

use function chmod;
use function dirname;
use function fileperms;
use function mkdir;
use function rmdir;
use function symlink;
use function uniqid;

use const DIRECTORY_SEPARATOR;

class PrivateTmpDirTest extends TestCase
{
    private string $baseDir;

    protected function setUp(): void
    {
        if (DIRECTORY_SEPARATOR === '\\') {
            $this->markTestSkipped('No POSIX owner/mode on Windows.');
        }

        $this->baseDir = __DIR__ . '/tmp/' . uniqid('base');
        mkdir($this->baseDir);
    }

    public function testDirIsCreatedForTheCurrentUserOnly(): void
    {
        $dir = (new PrivateTmpDir())($this->baseDir);

        $this->assertSame($this->baseDir, dirname($dir));
        $this->assertSame(0700, (int) fileperms($dir) & 0777);
        $this->assertSame($dir, (new PrivateTmpDir())($this->baseDir));
    }

    public function testDirWritableByOthersIsRejected(): void
    {
        $dir = (new PrivateTmpDir())($this->baseDir);
        chmod($dir, 0777);

        $this->expectException(DirectoryNotPrivate::class);
        (new PrivateTmpDir())($this->baseDir);
    }

    public function testSymlinkedDirIsRejected(): void
    {
        $dir = (new PrivateTmpDir())($this->baseDir);
        rmdir($dir);
        $ownDir = $this->baseDir . '/own';
        mkdir($ownDir, 0700);
        symlink($ownDir, $dir);

        $this->expectException(DirectoryNotPrivate::class);
        (new PrivateTmpDir())($this->baseDir);
    }
}
