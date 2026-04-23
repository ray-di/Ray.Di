<?php

declare(strict_types=1);

namespace Ray\Di;

use Ray\Aop\MethodInterceptor;
use Ray\Aop\Pointcut;

/**
 * Ray.Di Domain Types for Psalm
 *
 * This file contains Psalm type definitions for the Ray.Di dependency injection framework.
 * These types enhance static analysis and provide better IDE support.
 *
 * Container and Registry Types
 *
 * @psalm-type DependencyContainer = array<non-empty-string, DependencyInterface>
 * @psalm-type DependencyIndex = non-empty-string
 * @psalm-type PointcutList = array<int, Pointcut>
 * @psalm-type BindingName = non-empty-string
 * @psalm-type BindableInterface = class-string|''
 * @psalm-type ConstructorNameMapping = array<non-empty-string, non-empty-string>
 * @psalm-type ParameterNameMapping = array<string, string>
 * @psalm-type NamedParameterString = non-empty-string
 * @psalm-type ScriptDir = non-empty-string
 *
 * Enhanced Injection and Argument Types
 * @psalm-type InjectableValue object|scalar|array<array-key, (object|scalar|null)>|null
 * @psalm-type InjectionPointDefinition = array{0: string, 1: string, 2: bool}
 * @psalm-type InjectionPointsList = list<InjectionPointDefinition>
 * @psalm-type MethodArguments = list<mixed>
 * @psalm-type ArgumentSerializationData = array{0: DependencyIndex, 1: bool, 2: string, 3: string, 4: string, 5: array{0: string, 1: string, 2: string}}
 * @psalm-type UnboundTypeList = list<'bool'|'int'|'float'|'string'|'array'|'resource'|'callable'|'iterable'|'object'|'mixed'>
 * @psalm-type QualifierList = array<object>
 *
 * Scope and Lifecycle Types
 * @psalm-type ScopeType = Scope::SINGLETON|Scope::PROTOTYPE
 * @psalm-type ProviderContext = string
 *
 * MultiBinding Types
 * @psalm-type MultiBindingMap = array<string, non-empty-array<array-key, MultiBinding\LazyInterface>>
 * @psalm-type LazyBindingList = non-empty-array<array-key, MultiBinding\LazyInterface>
 *
 * AOP and Aspect Types
 * @psalm-type MethodInterceptorBindings = array<non-empty-string, list<MethodInterceptor>>
 * @psalm-type InterceptorClassList = array<class-string<MethodInterceptor>>
 * @psalm-type VisitorResult = object|array<array-key, mixed>|null
 *
 * Reflection and Metadata Types
 * @psalm-type ReflectionMethodReference = array{0: string, 1: string, 2: string}
 * @psalm-type DependencyMeta = string
 *
 * Exception and Error Types
 * @psalm-type DiException = Exception\Unbound|Exception\Untargeted|Exception\NotFound|Exception\InvalidProvider|Exception\InvalidType
 *
 * Annotation Types
 * @psalm-type AnnotationType = Di\Named|Di\Inject|Di\Qualifier|Di\PostConstruct|Di\Assisted|Di\Set<object>
 * @psalm-type DependencyImplementation = Dependency|DependencyProvider|Instance|NullDependency|NullObjectDependency
 * @psalm-type LazyImplementation = MultiBinding\LazyInstance<mixed>|MultiBinding\LazyProvider<ProviderInterface<mixed>>|MultiBinding\LazyTo<object>
 *
 * Core Component Types
 * @psalm-type SetterMethodsList = array<SetterMethod>
 * @psalm-type ArgumentsList = array<Argument>
 *
 * Domain-Specific Array Types
 * @psalm-type ModuleList = non-empty-array<AbstractModule>
 * @psalm-type NamedArguments = array<string, InjectableValue>
 */
final class Types
{
}
