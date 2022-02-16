## Instance Bindings

You can bind a type to an instance of that type. This is usually only useful for objects that don't have dependencies of their own, such as value objects:

```php
$this->bind(UserInterface::class)->toInstance(new User);
```
```php
$this->bind()->annotatedWith('login_id')->toInstance('bear');
```

Avoid using `toInstance()` with objects that are complicated to create, since it can slow down application startup.

