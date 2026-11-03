<?php

declare(strict_types=1);

namespace Ray\Di;

use Ray\Aop\AbstractMatcher;
use Ray\Aop\BuiltinMatcher;
use Throwable;

use function count;
use function file_get_contents;
use function file_put_contents;
use function hash;
use function hash_equals;
use function implode;
use function is_file;
use function is_object;
use function is_string;
use function ksort;
use function preg_match;
use function rename;
use function serialize;
use function sort;
use function sprintf;
use function tempnam;
use function unlink;

/**
 * Render a composed container or write it as bindings.md
 *
 * Best-effort and atomic: a diagnostics artifact must never break its
 * caller, and a concurrent writer must never leave a torn file behind.
 * The file ends with a signature trailer so an unchanged binding surface
 * skips rewriting.
 */
final class BindingsMarkdown
{
    /** Bump when the rendered format or signature inputs change. */
    private const SIGNATURE_VERSION = 3;

    public function __invoke(Container $container, string $classDir): void
    {
        try {
            $file = $classDir . '/bindings.md';
            $signature = $this->signature($container);
            if ($signature !== null && $this->isCached($file, $signature)) {
                return;
            }

            $markdown = $this->render($container);
            if ($signature !== null) {
                $markdown .= sprintf("\n<!-- signature: %s -->\n", $signature);
            }

            $tmpFile = @tempnam($classDir, 'bindings');
            if ($tmpFile === false) { // @codeCoverageIgnoreStart
                return;
            } // @codeCoverageIgnoreEnd

            $renamed = file_put_contents($tmpFile, $markdown) !== false && @rename($tmpFile, $file);
            if (! $renamed) { // @codeCoverageIgnoreStart
                @unlink($tmpFile);
            } // @codeCoverageIgnoreEnd
        } catch (Throwable) { // @codeCoverageIgnoreStart
            // best-effort: never break the caller for a diagnostics file
        } // @codeCoverageIgnoreEnd
    }

    /**
     * Return a signature of inputs that affect resolved bindings
     *
     * Composition provenance is intentionally excluded: if the final bindings
     * and pointcuts are unchanged, the existing diagnostics file is reused.
     */
    private function signature(Container $container): string|null
    {
        try {
            $bindings = [];
            foreach ($container->getContainer() as $index => $dependency) {
                $bindings[] = $index . "\0" . (string) $dependency;
            }

            sort($bindings);

            return hash(
                'sha256',
                serialize([self::SIGNATURE_VERSION, $bindings, $this->pointcutSignature($container)]),
            );
        } catch (Throwable) { // @codeCoverageIgnoreStart
            return null;
        } // @codeCoverageIgnoreEnd
    }

    /** @return list<array{string, string, string, list<string>}> */
    private function pointcutSignature(Container $container): array
    {
        $signature = [];
        foreach ($container->getPointcuts() as $pointcut) {
            $interceptors = [];
            foreach ($pointcut->interceptors as $interceptor) {
                $interceptors[] = is_object($interceptor) ? $interceptor::class : $interceptor;
            }

            $signature[] = [
                $pointcut::class,
                $this->matcherSignature($pointcut->classMatcher),
                $this->matcherSignature($pointcut->methodMatcher),
                $interceptors,
            ];
        }

        return $signature;
    }

    private function matcherSignature(AbstractMatcher $matcher): string
    {
        if ($matcher instanceof BuiltinMatcher) {
            return serialize($matcher);
        }

        return serialize([$matcher::class, $matcher->getArguments()]);
    }

    private function isCached(string $file, string $signature): bool
    {
        if (! is_file($file)) {
            return false;
        }

        $markdown = file_get_contents($file);
        if (! is_string($markdown)) {
            return false; // @codeCoverageIgnore
        }

        if (preg_match('/<!-- signature: ([0-9a-f]{64}) -->\s*$/', $markdown, $matches) !== 1) {
            return false;
        }

        return hash_equals($matches[1], $signature);
    }

    /** @return non-empty-string */
    public function render(Container $container): string
    {
        $log = $container->log;
        $replaced = 0;
        $discarded = 0;
        foreach ($log->getEvents() as $event) {
            if ($event->type === BindingEvent::REPLACE) {
                $replaced++;
            }

            if ($event->type === BindingEvent::KEEP) {
                $discarded++;
            }
        }

        /** @var array<string, int> $moduleCounts */
        $moduleCounts = [];
        foreach ($log->getSources() as $module) {
            $moduleCounts[$module] = ($moduleCounts[$module] ?? 0) + 1;
        }

        ksort($moduleCounts);
        $moduleLines = [];
        foreach ($moduleCounts as $module => $count) {
            $moduleLines[] = sprintf('- %s (%d)', $module, $count);
        }

        return sprintf(
            "# Ray.Di bindings\n\n%d bindings · %d modules · %d replaced · %d discarded\n\n"
                . "## Bindings\n\n%s\n\n## Modules\n\n%s\n\n## Provenance\n\n%s\n",
            count($container->getContainer()),
            count($moduleCounts),
            $replaced,
            $discarded,
            (new ModuleString())($container, $container->getPointcuts()),
            implode("\n", $moduleLines),
            $log,
        );
    }
}
