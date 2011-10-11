##Annotation Based Dependency Injection for PHP 5.3+##

This project was created in order to get Guice style dependency injection in PHP projects. It tries to mirror Guice's behavior and style. Guice is a Java dependency injection framework developed by Google (see http://code.google.com/p/google-guice/wiki/Motivation?tm=6).

Not all features of Guice have been implemented.

<b>This is a preview release.</b>

This software is based on Aura.Di.<br>
Read Documentation for Aura.Di: [http://auraphp.github.com/Aura.Di](http://auraphp.github.com/Aura.Di)

### Getting Started ###
We first need to tell it how to map our interfaces to their implementations.
his configuration is done in a module, which is any PHP class that implements the AbstractModule class:

	class BillingModule extends AbstractModule {
	    protected function configure()
	    {
	        $this->bind('TransactionLog')->to('DatabaseTransactionLog');
	        $this->bind('CreditCardProcessor')->to('PaypalCreditCardProcessor');
	        $this->bind('BillingService')->to('RealBillingService');
	    }
	}

We add @Inject to RealBillingService's constructor, which directs Injector to use it.
Injector will inspect the annotated constructor, and lookup values for each of parameter.

	class RealBillingService implements BillingService
	{
	    /**
	     * @var CreditCardProcessor
	     */
	    private $processor;

	    /**
	     * @var TransactionLog
	     */
	    private $transactionLog;

	    /**
	     * @Inject
	     */
	    public function __construct(CreditCardProcessor $processor, TransactionLog $transactionLog) {
	        $this->processor = $processor;
	        $this->transactionLog = $transactionLog;
	    }

	    public function chargeOrder(PizzaOrder $order, CreditCard $creditCard) {
	        try {
	            $result = $this->processor->charge($creditCard, $order->getAmount());
	            $this->transactionLog->logChargeResult($result);

	            return $result->wasSuccessful()
	            ? Receipt::forSuccessfulCharge($order->getAmount())
	            : Receipt::forDeclinedCharge($result->getDeclineMessage());
	        } catch (UnreachableException $e) {
	            $this->transactionLog->logConnectException($e);
	            return Receipt::forSystemFailure($e->getMessage());
	        }
	    }
	}

Finally, we can put it all together. The Injector can be used to get an instance of any of the bound classes.

	$injector = new	 Injector(new Container(new Forge(new Config(new Annotation))), new BillingModule);
	$billingService = $injector->getInstance('BillingService');

##Bindings##
###Linked Binding###
	/**
	 * Linked Binding
	 *
	 * Linked bindings map a type to its implementation.
	 */
	class BillingModule extends AbstractModule {
	    protected function configure() {
	        $this->bind('TransactionLogInterface')->to('DatabaseTransactionLog');
	    }
	}
###Binding annotation###
	/**
	 * Binding annotation
	 *
	 * The annotation and type together uniquely identify a binding.
	 */
	class BillingModule extends AbstractModule {
	    protected function configure() {
	        $this->bind('TransactionLogInterface')->annotatedWith('Db')->to('DatabaseTransactionLog');
	    }
	}
###Instance Bindings###
	/**
	 * Instance Bindings
	 *
	 * You can bind a type to a specific instance of that type.
	 * Avoid using .toInstance with objects that are complicated to create, since it can slow down application startup.
	 * You can use an @Provides method instead.
	 */
	class BillingModule extends AbstractModule {
	    protected function configure() {
	        $instance = new DatabaseTransactionLog;
	        $this->bind('TransactionLogInterface')->toInantance($instance);
	    }
	}
####Provider interface####

	/**
	 * Provider interface
	 */
	interface provider
	{
	    public function get();
	}
####Provider class####

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
###Provider Bindings###

	/**
	 * Provider Bindings
	 *
	 * The provider class implements Provider interface, which is a simple, general interface for supplying values:
	 */
	class BillingModule extends AbstractModule {
	    protected function configure() {
	        $this->bind('TransactionLogInterface')->toProvider('DatabaseTransactionLogProvider');
	    }
	}
###Just-in-time Bindings###
When the injector needs an instance of a type, it needs a binding.
The bindings in a modules are called explicit bindings, and the injector uses them whenever they're available.
If a type is needed but there isn't an explicit binding, the injector will attempt to create a Just-In-Time binding.
These are also known as JIT bindings and implicit bindings.

Annotate types tell the injector what their default implementation type is.
####@ImplementedBy####
The @ImplementedBy annotation acts like a linked binding, specifying the subtype to use when building a type.

	/**
	 * @ImplementedBy("PayPalCreditCardProcessor")
	 */
	interface CreditCardProcessor {
	    public function charge(CreditCard $creditCard);
	}
####@ProvidedBy####
ProvidedBy tells the injector about a Provider class that produces instances:

	/**
	 * @ProvidedBy("DatabaseTransactionLogProvider")
	 */
	interface TransactionLog {
	    public function logConnectException(UnreachableException $e);
	    public function logChargeResult(ChargeResult $result);
	}
## Scopes ##
Specify the scope for a type by applying the scope annotation to the implementation class.
As well as being functional, this annotation also serves as documentation.

	/**
	 * @Scope("Singleton")
	 */
	class InMemoryTransactionLog implements TransactionLog
	{
	}
Scopes can also be configured in bind statements:

	class BillingModule extends AbstractModule {
	    protected function configure() {
	        $this->bind('TransactionLog').to('InMemoryTransactionLog').in(Scope::SINGLETON);
	    }
	}
