## Provider Bindings

Provider bindings map a type to its provider.

```php
$this->bind(TransactionLogInterface::class)->toProvider(DatabaseTransactionLogProvider::class);
```
The provider class implements Ray's Provider interface, which is a simple, general interface for supplying values:

```php
namespace Ray\Di;

interface ProviderInterface
{
    public function get();
}
```
Our provider implementation class has dependencies of its own, which it receives via a contructor.
It implements the Provider interface to define what's returned with complete type safety:

```php

use Ray\Di\Di\Inject;
use Ray\Di\ProviderInterface;

class DatabaseTransactionLogProvider implements ProviderInterface
{
    public function __construct(
        private readonly ConnectionInterface $connection)
    ){}

    public function get()
    {
        $transactionLog = new DatabaseTransactionLog;
        $transactionLog->setConnection($this->connection);

        return $transactionLog;
    }
}
```

Finally we bind to the provider using the `toProvider()` method:

```php
$this->bind(TransactionLogInterface::class)->toProvider(DatabaseTransactionLogProvider::class);
```

## Injection Point

An **InjectionPoint** is a class that has information about an injection point.
It provides access to metadata via `\ReflectionParameter` or an attribute in `Provider`.

For example, the following `get()` method of `Psr3LoggerProvider` class creates injectable Loggers. The log category of a Logger depends upon the class of the object into which it is injected.

```php
class Psr3LoggerProvider implements ProviderInterface
{
    public function __construct(
        private InjectionPointInterface $ip
    ){}

    public function get()
    {
        $logger = new \Monolog\Logger($this->ip->getClass()->getName());
        $logger->pushHandler(new StreamHandler('path/to/your.log', Logger::WARNING));

        return $logger;
    }
}
```
`InjectionPointInterface` provides following methods.

```php
$ip->getClass();      // \ReflectionClass
$ip->getMethod();     // \ReflectionMethod
$ip->getParameter();  // \ReflectionParameter
$ip->getQualifiers(); // (array) $qualifierAnnotations
```
