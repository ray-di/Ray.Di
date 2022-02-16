## Untargeted Bindings

You may create bindings without specifying a target. This is most useful for concrete classes. An untargetted binding informs the injector about a type, so it may prepare dependencies eagerly. Untargetted bindings have no _to_ clause, like so:

```php
$this->bind(MyConcreteClass::class);
$this->bind(AnotherConcreteClass::class)->in(Scope::SINGLETON);
```

note: annotations are not supported for Untargeted Bindings
