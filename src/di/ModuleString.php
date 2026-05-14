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
use function is_string;
use function ksort;
use function preg_replace;
use function serialize;
use function sort;
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

        $lines = ['module'];
        $interfaces = array_keys($groups);
        $interfaceCount = count($interfaces);

        foreach ($interfaces as $i => $interface) {
            $isLast = $i === $interfaceCount - 1;
            $branch = $isLast ? '└──' : '├──';
            $continuation = $isLast ? '    ' : '│   ';

            $groupLines = $this->renderGroup($interface, $groups[$interface]);
            $lines[] = $branch . ' ' . $groupLines[0];
            for ($j = 1, $n = count($groupLines); $j < $n; $j++) {
                $lines[] = $continuation . $groupLines[$j];
            }
        }

        return implode(PHP_EOL, $lines);
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
            [$interface, $name] = BindingIndex::parse($dependencyIndex);
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
     * @param list<array{name: string, dependency: DependencyInterface, aop: array<string, list<string>>}> $bindings
     *
     * @return list<string>
     */
    private function renderGroup(string $interface, array $bindings): array
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
                foreach ($this->renderAop($binding['aop']) as $aopLine) {
                    $lines[] = $aopPrefix . $aopLine;
                }
            }
        }

        return $lines;
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
            /** @var string $class preg_replace won't return null with valid pattern */
            $class = preg_replace('/^\(dependency\) ([^\s]+).*$/', '$1', $str);

            return 'to:' . $class;
        }

        if ($dependency instanceof DependencyProvider) {
            $str = (string) $dependency;
            /** @var string $class preg_replace won't return null with valid pattern */
            $class = preg_replace('/^\(provider\) \(dependency\) ([^\s]+).*$/', '$1', $str);

            return 'toProvider:' . $class;
        }

        if ($dependency instanceof Instance) {
            return 'toInstance:' . $this->renderValue($dependency->value);
        }

        if ($dependency instanceof NullObjectDependency) {
            return 'toNull';
        }

        return (string) $dependency; // @codeCoverageIgnore
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
            return is_string($value) ? var_export($value, true) : (string) $value;
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
     *
     * @return list<string>
     */
    private function renderAop(array $aop): array
    {
        $methods = array_keys($aop);
        sort($methods);
        $lines = [];
        $count = count($methods);

        foreach ($methods as $i => $method) {
            $isLast = $i === $count - 1;
            $branch = $isLast ? '└─intercept─' : '├─intercept─';
            $lines[] = $branch . ' ' . $method . ': ' . implode(', ', $aop[$method]);
        }

        return $lines;
    }
}
