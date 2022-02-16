## Contextual Provider Bindings

You may want to create an object using the context when binding with Provider. For example, you want to inject different connection destinations on the same DB interface. In such a case, we bind it by specifying the context (string) with `toProvider ()`.


```php
$dbConfig = ['user' => $userDsn, 'job'=> $jobDsn, 'log' => $logDsn];
$this->bind()->annotatedWith('db_config')->toInstance(dbConfig);
$this->bind(Connection::class)->annotatedWith('usr_db')->toProvider(DbalProvider::class, 'user');
$this->bind(Connection::class)->annotatedWith('job_db')->toProvider(DbalProvider::class, 'job');
$this->bind(Connection::class)->annotatedWith('log_db')->toProvider(DbalProvider::class, 'log');
```

Providers are created for each context.

```php
use Ray\Di\Di\Inject;
use Ray\Di\Di\Named;

class DbalProvider implements ProviderInterface, SetContextInterface
{
    private $dbConfigs;

    public function setContext($context)
    {
        $this->context = $context;
    }

    public function __construct(#[Named('db_config') array $dbConfigs)
    {
        $this->dbConfigs = $dbConfigs;
    }

    /**
     * {@inheritdoc}
     */
    public function get()
    {
        $config = $this->dbConfigs[$this->context];
        $conn = DriverManager::getConnection($config);

        return $conn;
    }
}
```

It is the same interface, but you can receive different connections made by `Provider`.

```php
public function __construct(#[Named('user')] Connection $userDb, #[Named('job')] Connection $jobDb, #[Named('log') Connection $logDb)
{
  //...
}
```
