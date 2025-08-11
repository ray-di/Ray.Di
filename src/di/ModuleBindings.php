<?php

declare(strict_types=1);

namespace Ray\Di;

use Ray\Di\Bindings\BindingInfo;
use function assert;
use function explode;
use function get_class;
use function is_object;
use function serialize;
use function unserialize;

/**
 * Provides machine-readable binding information from raw binding data
 *
 * @psalm-import-type PointcutList from Types
 */
final class ModuleBindings
{
    /**
     * Get all bindings as machine-readable BindingInfo objects
     *
     * @param PointcutList $pointcuts
     *
     * @return list<BindingInfo>
     */
    public function __invoke(Container $container, array $pointcuts): array
    {
        $bindings = [];
        /** @psalm-suppress MixedAssignment */
        $container = unserialize(serialize($container), ['allowed_classes' => true]);
        assert($container instanceof Container);
        $spy = new SpyCompiler();

        foreach ($container->getContainer() as $dependencyIndex => $dependency) {
            if ($dependency instanceof Dependency) {
                $dependency->weaveAspects($spy, $pointcuts);
            }

            $bindingInfo = $this->parseBinding($dependencyIndex, $dependency);
            if ($bindingInfo !== null) {
                $bindings[] = $bindingInfo;
            }
        }

        return $bindings;
    }

    /**
     * @return ?BindingInfo
     */
    private function parseBinding(string $dependencyIndex, DependencyInterface $dependency): ?BindingInfo
    {
        // Parse index format: "interface-named" or just "interface"
        $parts = explode('-', $dependencyIndex, 2);
        $interface = $parts[0];
        $named = isset($parts[1]) && $parts[1] !== '+' ? $parts[1] : null;

        $dependencyString = (string) $dependency;

        // Determine binding type and target
        if ($dependency instanceof Instance) {
            $value = $dependency->value;
            return new BindingInfo(
                $interface,
                $named,
                'toInstance',
                is_object($value) ? get_class($value) : $value
            );
        }

        if ($dependency instanceof DependencyProvider) {
            // Extract the provider class from the string representation
            if (preg_match('/\(provider\) \(dependency\) (.+?)(,|$)/', $dependencyString, $matches)) {
                return new BindingInfo(
                    $interface,
                    $named,
                    'toProvider',
                    $matches[1]
                );
            }
        }

        if ($dependency instanceof Dependency) {
            // Extract target class from NewInstance
            $target = (string) $dependency->accept(new class implements VisitorInterface {
                public function visitDependency($newInstance, $postConstruct, $isSingleton) {
                    return (string) $newInstance;
                }
                public function visitInstance($instance) { return null; }
                public function visitProvider($dependency, $context, $isSingleton) { return null; }
                public function visitAspectBind($bind) { return null; }
            });
            
            if ($target === '') {
                return null;
            }
            
            // Get AOP bindings directly from VO
            $aopInfo = $dependency->getAopInfo();
            $aopBindings = $aopInfo && $aopInfo->hasBindings() ? $aopInfo->methodBindings : null;
            
            return new BindingInfo(
                $interface,
                $named,
                'to',
                $target,
                $aopBindings
            );
        }

        return null;
    }
}