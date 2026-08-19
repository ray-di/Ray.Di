<?php

declare(strict_types=1);

namespace Ray\Di;

use Swoole\Coroutine;

use function extension_loaded;

/**
 * Swoole / OpenSwoole coroutine bridge
 *
 * Every reference to coroutine extension classes is isolated here so the
 * container never hard-depends on a coroutine extension. When no extension
 * is loaded, id() always returns 0 and the container behaves exactly as
 * before.
 *
 * @internal
 */
final class CoroutineContext
{
    /** @var (callable(): int)|null */
    private static $idResolver = null;

    /**
     * Return the current coroutine id, or 0 outside any coroutine
     *
     * cid 0 is the single shared context of non-coroutine execution and of
     * the main context when an extension is loaded.
     */
    public static function id(): int
    {
        $resolver = self::$idResolver ??= self::detectIdResolver();

        return $resolver();
    }

    /** @return callable(): int */
    private static function detectIdResolver(): callable
    {
        if (extension_loaded('swoole')) {
            return static function (): int {
                /** @psalm-suppress RedundantCast -- int for phpstan, which sees mixed via extension reflection */
                $cid = (int) Coroutine::getCid();

                return $cid > 0 ? $cid : 0;
            };
        }

        if (extension_loaded('openswoole')) {
            return static function (): int {
                /** @psalm-suppress RedundantCast -- int for phpstan, which sees mixed via extension reflection */
                $cid = (int) \OpenSwoole\Coroutine::getCid();

                return $cid > 0 ? $cid : 0;
            };
        }

        return static fn (): int => 0;
    }
}
