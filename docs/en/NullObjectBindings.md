## Null Object Binding

A Null Object is an object that implements an interface but whose methods do nothing.
When bound with `toNull()`, the code of the Null Object is generated from the interface and bound to the generated instance.
This is useful for testing and AOP.

```php
protected function configure()
{
    $this->bind(CreditCardProcessorInterface::class)->toNull();
}
```
