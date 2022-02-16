## Scopes

By default, Ray returns a new instance each time it supplies a value. This behaviour is configurable via scopes.

```php
use Ray\Di\Scope;

protected function configure()
{
    $this->bind(TransactionLogInterface::class)->to(InMemoryTransactionLog::class)->in(Scope::SINGLETON);
}
```
    
