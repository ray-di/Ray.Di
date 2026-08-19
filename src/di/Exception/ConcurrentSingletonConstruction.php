<?php

declare(strict_types=1);

namespace Ray\Di\Exception;

use LogicException;

/**
 * Message format: '{interface}-{name}' (the index whose construction was already in flight)
 *
 * Concurrent singleton construction across coroutines is not supported:
 * the builder holds no lock a second coroutine could wait on without
 * risking a cross-coroutine deadlock. Warm up singletons before the server
 * starts accepting requests (e.g. Ray.Compiler warmup) so construction
 * never races at request time.
 */
final class ConcurrentSingletonConstruction extends LogicException implements ExceptionInterface
{
}
