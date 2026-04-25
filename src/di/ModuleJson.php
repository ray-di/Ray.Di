<?php

declare(strict_types=1);

namespace Ray\Di;

use function gettype;
use function is_object;
use function is_scalar;
use function json_encode;
use function preg_replace;
use function serialize;
use function strrpos;
use function substr;
use function unserialize;

use const JSON_PRETTY_PRINT;
use const JSON_UNESCAPED_SLASHES;
use const JSON_UNESCAPED_UNICODE;

/**
 * @psalm-import-type PointcutList from Types
 * @psalm-type BindingType = 'class'|'provider'|'instance'
 * @psalm-type AopBindings = array<string, list<string>>
 * @psalm-type BindingEntry = array{interface: string, name: string, type: BindingType, to: mixed, aop?: AopBindings}
 * @psalm-type ModuleBindings = array{bindings: list<BindingEntry>}
 */
final class ModuleJson
{
    /**
     * @param PointcutList $pointcuts
     */
    public function __invoke(Container $container, array $pointcuts): string
    {
        $bindings = $this->getBindings($container, $pointcuts);

        $json = json_encode($bindings, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);

        return $json === false ? '{}' : $json;
    }

    /**
     * @param PointcutList $pointcuts
     *
     * @return ModuleBindings
     */
    public function getBindings(Container $container, array $pointcuts): array
    {
        /** @psalm-suppress MixedAssignment */
        $container = unserialize(serialize($container), ['allowed_classes' => true]);
        /** @var Container $container */
        $collector = new AopCollector();
        $entries = [];

        foreach ($container->getContainer() as $dependencyIndex => $dependency) {
            $aopBindings = [];
            if ($dependency instanceof Dependency) {
                $aopBindings = $collector->collect($dependency, $pointcuts);
            }

            $entry = $this->createEntry($dependencyIndex, $dependency, $aopBindings);
            if ($entry !== null) {
                $entries[] = $entry;
            }
        }

        return ['bindings' => $entries];
    }

    /**
     * @param AopBindings $aopBindings
     *
     * @return BindingEntry|null
     */
    private function createEntry(string $dependencyIndex, DependencyInterface $dependency, array $aopBindings): ?array
    {
        [$interface, $name] = $this->parseIndex($dependencyIndex);

        if ($dependency instanceof Dependency) {
            return $this->createDependencyEntry($interface, $name, $dependency, $aopBindings);
        }

        if ($dependency instanceof DependencyProvider) {
            return $this->createProviderEntry($interface, $name, $dependency);
        }

        if ($dependency instanceof Instance) {
            return $this->createInstanceEntry($interface, $name, $dependency);
        }

        return null;
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
     * @param AopBindings $aopBindings
     *
     * @return BindingEntry
     */
    private function createDependencyEntry(string $interface, string $name, Dependency $dependency, array $aopBindings): array
    {
        $dependencyString = (string) $dependency;
        // Extract class name from "(dependency) ClassName" or "(dependency) ClassName (aop) ..."
        $className = preg_replace('/^\(dependency\) ([^\s]+).*$/', '$1', $dependencyString) ?? '';

        $entry = [
            'interface' => $interface,
            'name' => $name,
            'type' => 'class',
            'to' => $className,
        ];

        if ($aopBindings !== []) {
            $entry['aop'] = $aopBindings;
        }

        return $entry;
    }

    /**
     * @return BindingEntry
     */
    private function createProviderEntry(string $interface, string $name, DependencyProvider $dependency): array
    {
        $providerString = (string) $dependency;
        // Extract provider class name from "(provider) (dependency) ProviderClassName"
        $providerClass = preg_replace('/^\(provider\) \(dependency\) ([^\s]+).*$/', '$1', $providerString) ?? '';

        return [
            'interface' => $interface,
            'name' => $name,
            'type' => 'provider',
            'to' => $providerClass,
        ];
    }

    /**
     * @return BindingEntry
     */
    private function createInstanceEntry(string $interface, string $name, Instance $dependency): array
    {
        /** @psalm-suppress MixedAssignment */
        $value = $dependency->value;

        return [
            'interface' => $interface,
            'name' => $name,
            'type' => 'instance',
            'to' => $this->formatValue($value),
        ];
    }

    /**
     * @param mixed $value
     *
     * @return mixed
     */
    private function formatValue($value)
    {
        if (is_scalar($value) || $value === null) {
            return $value;
        }

        if (is_object($value)) {
            return ['__class' => $value::class];
        }

        return ['__type' => gettype($value)];
    }
}
