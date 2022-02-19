## Linked Bindings

Linked bindings map a type to its implementation. This example maps the interface TransactionLogInterface to the implementation DatabaseTransactionLog:

For example, the following code links the concrete DatabaseTransactionLog class to a subclass:

```php
$this->bind(TransactionLogInterface::class)->to(DatabaseTransactionLog::class);
```
