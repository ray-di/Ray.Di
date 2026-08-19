# Test Suite Map

## Two layers

**Contract tests** pin *observable semantics*: which binding wins, what wraps
what, which lifecycle guarantees hold. They are the specification. A change
that turns one red is a backward-compatibility break by definition — fix the
change, not the test.

**Unit tests** pin the *mechanics* of individual classes (Bind, Container,
Argument, ...).

Coverage measures execution, not meaning. PR #319 inverted module composition
priority while 100% branch coverage stayed green, because every line still ran
— only the *winners* changed, and no test asserted a winner. The contract
layer exists so that class of change can never again pass silently.

## Contract map

| Contract | Test |
| --- | --- |
| Binding precedence: bind() / install() / constructor chain / override() | `ModuleCompositionTest` |
| AOP wrapping order across modules and install() | `ModuleCompositionTest` |
| MultiBinding collection order across modules | `ModuleCompositionTest` |
| MultiBinding visibility: every installed sibling contributes | `ModuleCompositionTest` |
| rename(): deferred application, reachable sources, error contract | `RenameTest` |
| Cycle detection vs legal re-entry (singleton `@PostConstruct`) | `CircularDependencyTest` |
| Assisted invocation: not available after the call, nested calls restore the outer invocation, coroutine interceptions isolated | `AssistedTest`, `MethodInvocationCoroutineTest` |
| Coroutine isolation: no false cycle across coroutines, no duplicate singleton construction, real cycles still detected | `CoroutineSafetyTest` |
| Singleton identity, serialization lifecycle | `DependencyTest`, `InjectorTest` |
| JIT (untargeted) binding: single construction, named requests fail fast | `InjectorTest` |
| Module-list merging (`new Injector([$m1, $m2])`): first module wins | `ModuleMergerTest` |
| Binding provenance & collision history | `BindingLogTest` |

## The precedence rules (formerly folklore, now written down)

For the same `{interface}-{name}` key:

| Route A | Route B | Winner |
| --- | --- | --- |
| own `bind()` | constructor-chained module | own `bind()` |
| own `install()` | constructor-chained module | own `install()` |
| `bind()` | `install()` (either order) | `bind()` |
| first `install()` | second `install()` | first |
| outer chain module | inner chain module | outer |
| `override()` target | anything already bound | `override()` target |

Mechanism: `bind()` registers by assignment (last write wins); `install()` and
constructor chaining merge with `+=` (existing entries win); `configure()` runs
**before** the constructor-chained module is merged (`AbstractModule::__construct`).
Pointcuts and multibindings append in the same order, so earlier-declared means
outermost interceptor and first Map entry.

## Assertion rules

- **Assert the winner**, not the type: concrete class
  (`assertInstanceOf(FakeRobot2::class, ...)`), instance identity
  (`assertSame`), or invocation order/result. Asserting only an interface
  passes no matter which binding won — it re-states what the type system
  already guarantees.
- **Collision cases are mandatory** when adding a new route by which bindings
  enter the container: pair the new route against every existing route on the
  same key.
- **Behavior change protocol**: commit a red contract test pinning the current
  semantics first, then the change that keeps it green (see 51692c8b / 569e871e).
