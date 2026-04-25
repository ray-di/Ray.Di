<?php

declare(strict_types=1);

namespace Ray\Di;

use function array_keys;
use function assert;
use function count;
use function gettype;
use function implode;
use function is_object;
use function is_scalar;
use function ksort;
use function preg_replace;
use function serialize;
use function sprintf;
use function strrpos;
use function substr;
use function unserialize;
use function usort;
use function var_export;

use const PHP_EOL;

/**
 * @psalm-import-type PointcutList from Types
 */
final class ModuleString
{
    /**
     * @param PointcutList $pointcuts
     */
    public function __invoke(Container $container, array $pointcuts): string
    {
        /** @psalm-suppress MixedAssignment */
        $container = unserialize(serialize($container), ['allowed_classes' => true]);
        assert($container instanceof Container);

        $groups = $this->groupByInterface($container, $pointcuts);
        ksort($groups);

        $output = [];
        foreach ($groups as $interface => $bindings) {
            $output[] = $this->renderGroup($interface, $bindings);
        }

        return implode(PHP_EOL . PHP_EOL, $output);
    }

    /**
     * @param PointcutList $pointcuts
     *
     * @return array<string, list<array{name: string, dependency: DependencyInterface, aop: array<string, list<string>>}>>
     */
    private function groupByInterface(Container $container, array $pointcuts): array
    {
        $collector = new AopCollector();
        $groups = [];

        foreach ($container->getContainer() as $dependencyIndex => $dependency) {
            [$interface, $name] = $this->parseIndex($dependencyIndex);
            $aop = $dependency instanceof Dependency
                ? $collector->collect($dependency, $pointcuts)
                : [];

            $groups[$interface][] = [
                'name' => $name,
                'dependency' => $dependency,
                'aop' => $aop,
            ];
        }

        return $groups;
    }

    /**
     * @return array{string, string}
     */
    private function parseIndex(string $index): array
    {
        $pos = strrpos($index, '-');
        if ($pos === false) {
            return [$index, Name::ANY];
        }

        return [substr($index, 0, $pos), substr($index, $pos + 1)];
    }

    /**
     * @param list<array{name: string, dependency: DependencyInterface, aop: array<string, list<string>>}> $bindings
     */
    private function renderGroup(string $interface, array $bindings): string
    {
        $header = $interface === '' ? "''" : $interface;
        $lines = [$header];

        usort($bindings, static fn (array $a, array $b): int => $a['name'] <=> $b['name']);

        $count = count($bindings);
        foreach ($bindings as $i => $binding) {
            $isLast = $i === $count - 1;
            $branch = $isLast ? '└──' : '├──';
            $lines[] = $branch . ' ' . $this->renderBinding($binding['name'], $binding['dependency']);

            if ($binding['aop'] !== []) {
                $aopPrefix = $isLast ? '    ' : '│   ';
                $lines[] = $aopPrefix . $this->renderAop($binding['aop']);
            }
        }

        return implode(PHP_EOL, $lines);
    }

    private function renderBinding(string $name, DependencyInterface $dependency): string
    {
        $parts = [];

        if ($name !== Name::ANY) {
            $parts[] = 'named:' . $name;
        }

        $parts[] = $this->renderTarget($dependency);

        $scope = $this->getScope($dependency);
        if ($scope === Scope::SINGLETON) {
            $parts[] = 'in:Singleton';
        }

        return implode(' ─ ', $parts);
    }

    private function renderTarget(DependencyInterface $dependency): string
    {
        if ($dependency instanceof Dependency) {
            $str = (string) $dependency;
            $class = preg_replace('/^\(dependency\) ([^\s]+).*$/', '$1', $str) ?? '';

            return 'to:' . $class;
        }

        if ($dependency instanceof DependencyProvider) {
            $str = (string) $dependency;
            $class = preg_replace('/^\(provider\) \(dependency\) ([^\s]+).*$/', '$1', $str) ?? '';

            return 'toProvider:' . $class;
        }

        if ($dependency instanceof Instance) {
            return 'toInstance:' . $this->renderValue($dependency->value);
        }

        return (string) $dependency;
    }

    /**
     * @param mixed $value
     */
    private function renderValue($value): string
    {
        if ($value === null) {
            return 'null';
        }

        if ($value === true) {
            return 'true';
        }

        if ($value === false) {
            return 'false';
        }

        if (is_scalar($value)) {
            if (gettype($value) === 'string') {
                return var_export($value, true);
            }

            return (string) $value;
        }

        if (is_object($value)) {
            return '(' . $value::class . ')';
        }

        return '(' . gettype($value) . ')';
    }

    private function getScope(DependencyInterface $dependency): string
    {
        if ($dependency instanceof Dependency && $dependency->isSingleton()) {
            return Scope::SINGLETON;
        }

        if ($dependency instanceof DependencyProvider && $dependency->isSingleton()) {
            return Scope::SINGLETON;
        }

        return Scope::PROTOTYPE;
    }

    /**
     * @param array<string, list<string>> $aop
     */
    private function renderAop(array $aop): string
    {
        $methods = array_keys($aop);
        $lines = [];
        $count = count($methods);

        foreach ($methods as $i => $method) {
            $isLast = $i === $count - 1;
            $branch = $isLast ? '└─' : '├─';
            $lines[] = $branch . ' ' . $method . ': ' . implode(', ', $aop[$method]);
        }

        return implode(PHP_EOL, $lines);
    }
}
