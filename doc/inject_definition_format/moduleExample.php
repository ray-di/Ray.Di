<?php
use Ray\Di\Di\Inject;

/**
 * Linked Binding
 *
 * Linked bindings map a type to its implementation.
 */
class BillingModule extends AbstractModule
{
    protected function configure()
    {
        $this->bind('TransactionLogInterface')->to('DatabaseTransactionLog');
    }
}
/**
 * Binding annotation
 *
 * The annotation and type together uniquely identify a binding.
 */
class BillingModule extends AbstractModule
{
    protected function configure()
    {
        $this->bind('TransactionLogInterface')->annotatedWith('Db')->to('DatabaseTransactionLog');
    }
}

/**
 * Instance Bindings
 *
 * You can bind a type to a specific instance of that type.
 * Avoid using .toInstance with objects that are complicated to create, since it can slow down application startup.
 * You can use an @Provides method instead.
 */
class BillingModule extends AbstractModule
{
    protected function configure()
    {
        $instance = new DatabaseTransactionLog;
        $this->bind('TransactionLogInterface')->toInantance($instance);
    }
}

/**
 * Provider interface
 */
interface provider
{
    public function get();
}

/**
 * DatabaseTransactionLog provider class
 */
class DatabaseTransactionLogProvider implements provider
{
    /**
     * @Inject
     */
    public function __construct(Di $di, Connection $connection)
    {
        $this->di = $di;
        $this->connection = $connection;
    }

    public function get()
    {
        $transactionLog = $this->di->newInstance('DatabaseTransactionLog');
        $transactionLog->setConnection($this->connection);

        return $transactionLog;
    }
}

/**
 * Provider Bindings
 *
 * The provider class implements Provider interface, which is a simple, general interface for supplying values:
 */
class BillingModule extends AbstractModule
{
    protected function configure()
    {
        $this->bind('TransactionLogInterface')->toProvider('DatabaseTransactionLogProvider');
    }
}

/**
 * Just-in-time Bindings
 *
 * @ImplementedBy("PayPalCreditCardProcessor")
 */
interface CreditCardProcessor
{
    public function charge(CreditCard $creditCard);
}

/**
 * Just-in-time Bindings
 *
 * When the injector needs an instance of a type, it needs a binding.
 * The bindings in a modules are called explicit bindings, and the injector uses them whenever they're available.
 * If a type is needed but there isn't an explicit binding, the injector will attempt to create a Just-In-Time binding.
 * These are also known as JIT bindings and implicit bindings.
 *
 * Annotate types tell the injector what their default implementation type is.
 * The @ImplementedBy annotation acts like a linked binding, specifying the subtype to use when building a type.
 * @ProvidedBy tells the injector about a Provider class that produces instances:
 *
 * @ProvidedBy("DatabaseTransactionLogProvider")
 */
interface TransactionLog
{
    public function logConnectException(UnreachableException $e);
    public function logChargeResult(ChargeResult $result);
}

/**
 * Scopes
 *
 * Specify the scope for a type by applying the scope annotation to the implementation class.
 * As well as being functional, this annotation also serves as documentation.
 *
 * @Scope("Singleton")
 */
class InMemoryTransactionLog implements TransactionLog
{
}

// Scopes can also be configured in bind statements:
class BillingModule extends AbstractModule
{
    protected function configure()
    {
        $this->bind('TransactionLog').to('InMemoryTransactionLog').in(Scope::SINGLETON);
    }
}
