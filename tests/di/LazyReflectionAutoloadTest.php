<?php

declare(strict_types=1);

namespace Ray\Di;

use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use RuntimeException;

use function escapeshellarg;
use function exec;
use function implode;
use function sprintf;
use function tempnam;
use function unlink;

use const PHP_BINARY;

/**
 * Argument and InjectionPoint must not force-autoload their declaring class
 * during unserialize(); only get()/getParameter() may trigger it.
 *
 * This can only be observed across a process boundary: building the
 * fixture's ReflectionParameter necessarily autoloads FakeLazyReflectionTarget
 * in whichever process builds it, so generation and verification run as two
 * separate PHP processes, each using the current codebase's live
 * __serialize()/__unserialize() rather than a committed blob.
 */
class LazyReflectionAutoloadTest extends TestCase
{
    #[DataProvider('targetProvider')]
    public function testDeclaringClassIsNotAutoloadedUntilFirstAccess(string $target): void
    {
        $scriptDir = __DIR__ . '/script';
        $blobFile = tempnam(__DIR__ . '/tmp', 'ray-di-lazy-');
        if ($blobFile === false) {
            throw new RuntimeException('tempnam() failed to create a blob file under tests/di/tmp');
        }

        try {
            exec(sprintf(
                '%s %s %s %s 2>&1',
                escapeshellarg(PHP_BINARY),
                escapeshellarg($scriptDir . '/lazy_reflection_generate.php'),
                escapeshellarg($target),
                escapeshellarg($blobFile)
            ), $genOutput, $genStatus);
            $this->assertSame(0, $genStatus, implode("\n", $genOutput));

            exec(sprintf(
                '%s %s %s %s 2>&1',
                escapeshellarg(PHP_BINARY),
                escapeshellarg($scriptDir . '/lazy_reflection_verify.php'),
                escapeshellarg($target),
                escapeshellarg($blobFile)
            ), $verifyOutput, $verifyStatus);

            $this->assertSame('PASS', implode('', $verifyOutput), implode("\n", $verifyOutput));
            $this->assertSame(0, $verifyStatus);
        } finally {
            unlink($blobFile);
        }
    }

    /** @return array<string, array{0: string}> */
    public static function targetProvider(): array
    {
        return [
            'Argument' => ['argument'],
            'InjectionPoint' => ['injectionpoint'],
        ];
    }
}
