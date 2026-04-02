<?php

declare(strict_types=1);

namespace Ray\Di;

use Ray\Di\Di\InjectInterface;
use Ray\Di\Di\Qualifier;
use ReflectionAttribute;
use ReflectionClass;
use ReflectionMethod;
use ReflectionParameter;

use function count;

/**
 * Backward compatible parameter qualifier for method-level Qualifier attributes
 *
 * Automatically applies method-level Qualifier attributes to parameters when:
 * - Parameters have no explicit qualifier
 * - For single-parameter methods: Qualifier is applied to the only parameter
 * - For multi-parameter methods: Qualifier's value property specifies the target parameter name
 * - For constructors: Method has a Qualifier attribute (InjectInterface is implicit)
 * - For setters: Method has an attribute implementing both InjectInterface and Qualifier
 *
 * This behavior is deprecated for the following reasons:
 * - Violates Single Responsibility Principle (one attribute serving dual purposes)
 * - Creates fragility when refactoring (adding parameters changes behavior)
 * - Reduces code clarity (implicit rather than explicit)
 *
 * @deprecated Use explicit separation: #[Inject] at method level, Qualifier at parameter level
 *
 * Recommended migration:
 *   OLD (implicit):
 *     #[FakeLogDbInject]
 *     public function setDb(ExtendedPdoInterface $pdo) { }
 *
 *   NEW (explicit):
 *     #[Inject]
 *     public function setDb(#[FakeLogDb] ExtendedPdoInterface $pdo) { }
 *
 * @internal
 */
final class BcParameterQualifier
{
    /**
     * Get parameter qualifier names from method-level attribute if applicable
     *
     * @param ReflectionMethod $method The method to analyze
     *
     * @return array<string, string> Parameter name to qualifier mapping (empty if not applicable)
     */
    public static function getNames(ReflectionMethod $method): array
    {
        $params = $method->getParameters();
        if ($params === []) {
            return [];
        }

        $isConstructor = $method->name === '__construct';
        $methodAttributes = $method->getAttributes();
        $names = [];

        foreach ($methodAttributes as $attr) {
            $attrClass = new ReflectionClass($attr->getName());
            if ($attrClass->getAttributes(Qualifier::class) === []) {
                continue;
            }

            $instance = $attr->newInstance();

            // For setters: Must also implement InjectInterface
            if (! $isConstructor && ! $instance instanceof InjectInterface) {
                continue;
            }

            $targetParam = self::resolveTargetParam($instance, $params);
            if ($targetParam === null) {
                continue;
            }

            // Skip if parameter already has a qualifier
            if (self::hasParameterQualifier($targetParam->getAttributes())) {
                continue;
            }

            $names[$targetParam->name] = $attr->getName();
        }

        return $names;
    }

    /**
     * Resolve which parameter a Qualifier targets
     *
     * If the Qualifier has a value property matching a parameter name, use that.
     * Otherwise, if there is exactly one parameter, use it.
     *
     * @param object                 $qualifier The Qualifier attribute instance
     * @param array<ReflectionParameter> $params    Method parameters
     */
    private static function resolveTargetParam(object $qualifier, array $params): ?ReflectionParameter
    {
        if (count($params) === 1) {
            return $params[0];
        }

        // For multi-parameter methods, use qualifier's value property to find target
        if (isset($qualifier->value) && $qualifier->value !== '') {
            foreach ($params as $param) {
                if ($param->name === $qualifier->value) {
                    return $param;
                }
            }
        }

        return null;
    }

    /**
     * Check if parameter already has a qualifier attribute
     *
     * @param array<ReflectionAttribute> $attributes
     */
    private static function hasParameterQualifier(array $attributes): bool
    {
        foreach ($attributes as $attr) {
            $attrClass = new ReflectionClass($attr->getName());

            // Check for Qualifier marker
            if ($attrClass->getAttributes(Qualifier::class) !== []) {
                return true;
            }
        }

        return false;
    }
}
