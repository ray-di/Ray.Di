# Ray.Di
## Annotation based dependency injection for PHP

## Overview ##
This project was created in order to get Guice style dependency injection in PHP projects. It tries to mirror Guice's behavior and style. Guice is a Java dependency injection framework developed by Google (see http://code.google.com/p/google-guice/wiki/Motivation?tm=6). 

This package also supports some of the JSR-330 object lifecycle annotations, like @PostConstruct, @PreDestroy. 

 * This is a preview release.
 * Not all features of Guice have been implemented.

## Aura.Di ##
This project use Aura.Di component. 

"simple, elegant, and contains some of the cleanest PHP you will see anywhere."

Aura.Di: [http://auraphp.github.com/Aura.Di](http://auraphp.github.com/Aura.Di)

## Getting Started ##

With dependency injection, objects accept dependencies in their constructors. To construct an object, you first build its dependencies. But to build each dependency, you need its dependencies, and so on. So when you build an object, you really need to build an object graph.

Building object graphs by hand is labour intensive, error prone, and makes testing difficult. Instead, this package can build the object graph for you. But first, this package needs to be configured to build the graph exactly as you want it.

To illustrate, we'll start the RealBillingService class that accepts its dependent interfaces CreditCardProcessor and TransactionLog in its constructor. To make it explicit that the RealBillingService constructor is invoked by this package, we add the @Inject annotation:

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
	    public function __construct(CreditCardProcessor $processor, TransactionLog $transactionLog)
		{
	        $this->processor = $processor;
	        $this->transactionLog = $transactionLog;
	    }

	    public function chargeOrder(PizzaOrder $order, CreditCard $creditCard)
		{
		...
		}
}

We want to build a RealBillingService using PaypalCreditCardProcessor and DatabaseTransactionLog. this package uses bindings to map types to their implementations. A module is a collection of bindings specified using fluent, English-like method calls: 

	public class BillingModule extends AbstractModule {

	    protected function configure() {

	        /*
	         * This tells this package that whenever it sees a dependency on a TransactionLog,
	         * it should satisfy the dependency using a DatabaseTransactionLog.
	         */
	        $this->bind('TransactionLog')->to('DatabaseTransactionLog');

	        /*
	         * Similarly, this binding tells this package that when CreditCardProcessor is used in
	         * a dependency, that should be satisfied with a PaypalCreditCardProcessor.
	         */
	        $this->bind('CreditCardProcessor')->to('PaypalCreditCardProcessor');
	    }
	}
The modules are the building blocks of an injector, which is this package's object-graph builder. First we create the injector, and then we can use that to build the RealBillingService: 	

	$injector = new	Injector(new Container(new Forge(new Config(new Annotation))), new BillingModule);
	$billingService = $injector->getInstance('BillingService');

By building the billingService, we've constructed a small object graph using Guice. The graph contains the billing service and its dependent credit card processor and transaction log. 

##Bindings##
The injector's job is to assemble graphs of objects. You request an instance of a given type, and it figures out what to build, resolves dependencies, and wires everything together. To specify how dependencies are resolved, configure your injector with bindings. 

###Creating Bindings###

To create bindings, extend AbstractModule and override its configure method. In the method body, call bind() to specify each binding. 

When a dependency is requested but not found it attempts to create a just-in-time binding. The injector also includes bindings for the providers of its other bindings. 

###Linked Binding###
Linked bindings map a type to its implementation. 

	class BillingModule extends AbstractModule {
	    protected function configure() {
	        $this->bind('TransactionLogInterface')->to('DatabaseTransactionLog');
	    }
	}
###Binding annotation###
The annotation and type together uniquely identify a binding.

	class BillingModule extends AbstractModule {
	    protected function configure() {
	        $this->bind('TransactionLogInterface')->annotatedWith('Db')->to('DatabaseTransactionLog');
	    }
	}
###Instance Bindings###
You can bind a type to a specific instance of that type.
Avoid using .toInstance with objects that are complicated to create, since it can slow down application startup.

	class BillingModule extends AbstractModule {
	    protected function configure() {
	        $instance = new DatabaseTransactionLog;
	        $this->bind('TransactionLogInterface')->toInantance($instance);
	    }
	}
###Provider Bindings###


####Provider interface####
The provider class implements Provider interface, which is a simple, general interface for supplying values:

	interface provider
	{
	    public function get();
	}

####Provider class####
Our provider implementation class has dependencies of its own, which it receives via its @Inject-annotated constructor. It implements the Provider interface to define what's returned with complete type safety: 

	class DatabaseTransactionLogProvider implements provider
	{
	    /**
	     * @Inject
	     */
	    public function __construct(Connection $connection)
	    {
	        $this->connection = $connection;
	    }

	    public function get()
	    {
	        $transactionLog = new DatabaseTransactionLog;
	        $transactionLog->setConnection($this->connection);
	        return $transactionLog;
	    }
	}
Finally we bind to the provider using the .toProvider clause: 

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
## Lifecycle Annotations ##
The @PostConstruct and @PreDestroy annotations can be used to trigger initialization and destruction callbacks respectively.

###@PostConstruct###
The PostConstruct annotation is used on a method that needs to be executed after dependency injection is done to perform any initialization. 

###@PreDestroy###
The PreDestroy annotation is used on methods as a callback notification to signal that the instance is in the process of being removed by the container. The method annotated with PreDestroy is typically used to release resources that it has been holding.

##Unimplemented##

 * Eager singletons
 * Constructor bindings
 * Stage
 * Binding for the Logger
 * Custom scope annotations
 * Chained linked bindings

# Aspect Oriented Programming #

To compliment dependency injection, Ray.Di with Ray.Aop supports method interception. This feature enables you to write code that is executed each time a matching method is invoked. It's suited for cross cutting concerns ("aspects"), such as transactions, security and logging. Because interceptors divide a problem into aspects rather than objects, their use is called Aspect Oriented Programming (AOP).


## Injecting Interceptors

To mark select methods as weekdays-only, we define an annotation and apply. @Aspect in class, @NotOnWeekends in method.

	/**
	 * @Aspect
	 */
	class RealBillingService
	{
	    /**
	     * @NotOnWeekends
	     */
	    chargeOrder(PizzaOrder $order, CreditCard $creditCard)
	    {
		

Next, we define the interceptor by implementing the Ray\Aop\MethodInterceptor interface. When we need to call through to the underlying method, we do so by calling $invocation->proceed(): 

	use Ray\Aop\MethodInterceptor,
	    Ray\Aop\MethodInvocation;

	class WeekendBlocker implements MethodInterceptor
	{
	    public function invoke(MethodInvocation $invocation)
	    {
	        $today = getdate();
	        if ($today['weekday'][0] === 'S') {
	            throw new \RuntimeException(
	          		$invocation->getMethod()->getName() . " not allowed on weekends!"
	            );
	        }
	        return $invocation->proceed();
	    }
	}

Finally, we configure everything. We can register intercept annotation or use matchers for the classes and methods to be intercepted. 

	class NotOnWeekendsModule extends AbstractModule
	{
	    protected function configure()
	    {
	        $this->registerInterceptorAnnotation(
		        'NotOnWeekends',
		        array(new WeekendBlocker)
		    );
		
	        // or use matcher binding	
	        //$classMatcher = function($className){ ...};
	        //$methodMatcher = function($methodName){ ...};
			//$this->bindInterceptor(
			//    $classMatcher,
			//    $methodMatcher,
			//    array(new WeekendBlocker)
			//);
	    }
	}

Putting it all together, (and waiting until Saturday), we see the method is intercepted and our order is rejected: 

	RuntimeException: chargeOrder not allowed on weekends! in /apps/pizza/WeekendBlocker.php on line 14

	Call Stack:
	    0.0022     228296   1. {main}() /apps/pizza/main.php:0
	    0.0054     317424   2. Ray\Aop\Weaver->chargeOrder() /apps/pizza/main.php:14
	    0.0054     317608   3. Ray\Aop\Weaver->__call() /libs/Ray.Aop/src/Weaver.php:14
	    0.0055     318384   4. Ray\Aop\ReflectiveMethodInvocation->proceed() /libs/Ray.Aop/src/Weaver.php:68
	    0.0056     318784   5. Ray\Aop\Sample\WeekendBlocker->invoke() /libs/Ray.Aop/src/ReflectiveMethodInvocation.php:65

### AOP Alliance

The method interceptor API implemented by Ray.Di is a part of a public specification called AOP Alliance (JAVA). 

##Requiment##
 * PHP 5.3+ 

##Quick Start##
    $ git clone git@github.com:koriym/Ray.Di.git
    $ cd Ray.Di
    $ git submodule update --init
    // original
    $ php doc/sample-01-db/original.php
    // with Ray.DI (+transaction +timer +template interception.)
    $ php doc/sample-01-db/main.php
