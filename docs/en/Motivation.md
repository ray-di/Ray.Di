# Motivation

Wiring everything together is a tedious part of application development. There
are several approaches to connect data, service, and presentation classes to one
another. To contrast these approaches, we'll write the billing code for a pizza
ordering website:

```php
interface BillingServiceInterface
{
    /**
    * Attempts to charge the order to the credit card. Both successful and
    * failed transactions will be recorded.
    *
    * @return Receipt a receipt of the transaction. If the charge was successful,
    *      the receipt will be successful. Otherwise, the receipt will contain a
    *      decline note describing why the charge failed.
    */
    public function chargeOrder(PizzaOrder order, CreditCard creditCard): Receipt;
}
```

Along with the implementation, we'll write unit tests for our code. In the tests
we need a `FakeCreditCardProcessor` to avoid charging a real credit card!

## Direct constructor calls

Here's what the code looks like when we just `new` up the credit card processor
and transaction logger:

```php
public class RealBillingService implements BillingServiceInterface
{
    public function chargeOrder(PizzaOrder $order, CreditCard $creditCard): Receipt
    {
        $processor = new PaypalCreditCardProcessor();
        $transactionLog = new DatabaseTransactionLog();
    
    try {
        $result = $processor->charge($creditCard, $order->getAmount());
        $transactionLog->logChargeResult($result);
    
        return $result->wasSuccessful()
            ? Receipt::forSuccessfulCharge($order->getAmount())
            : Receipt::forDeclinedCharge($result->getDeclineMessage());
       } catch (UnreachableException $e) {
            $transactionLog->logConnectException($e);

            return ReceiptforSystemFailure($e->getMessage());
      }
    }
}
```

This code poses problems for modularity and testability. The direct,
compile-time dependency on the real credit card processor means that testing the
code will charge a credit card! It's also awkward to test what happens when the
charge is declined or when the service is unavailable.

## Factories

A factory class decouples the client and implementing class. A simple factory
uses static methods to get and set mock implementations for interfaces. A
factory is implemented with some boilerplate code:

```php
public class CreditCardProcessorFactory
{
    private static CreditCardProcessor $instance;
    
    public static setInstance(CreditCardProcessor $processor): void 
    {
        self::$instance = $processor;
    }
    
    public static function getInstance(): CreditCardProcessor
    {
        if (self::$instance == null) {
            return new SquareCreditCardProcessor();
        }
        
        return self::$instance;
    }
}
```

In our client code, we just replace the `new` calls with factory lookups:

```php
public class RealBillingService implements BillingServiceInterface
{
    public function chargeOrder(PizzaOrder $order, CreditCard $creditCard): Receipt
    {
        $processor = CreditCardProcessorFactory::getInstance();
        $transactionLog = TransactionLogFactory::getInstance();
        
        try {
            $result = $processor->charge($creditCard, $order->getAmount());
            $transactionLog->logChargeResult($result);
            
            return $result->wasSuccessful()
                ? Receipt::forSuccessfulCharge($order->getAmount())
                : Receipt::forDeclinedCharge($result->getDeclineMessage());
         } catch (UnreachableException $e) {
             $transactionLog->logConnectException($e);
             return Receipt::forSystemFailure($e.getMessage());
        }
    }
}
```

The factory makes it possible to write a proper unit test:

```php
public class RealBillingServiceTest extends TestCase 
{
    private PizzaOrder $order;
    private CreditCard $creditCard;
    private InMemoryTransactionLog $transactionLog
    private FakeCreditCardProcessor $processor;
    
    public function setUp(): void
    {
        $this->order = new PizzaOrder(100);
        $this->creditCard = new CreditCard('1234', 11, 2010);
        $this->processor = new FakeCreditCardProcessor();
        TransactionLogFactory::setInstance($transactionLog);
        CreditCardProcessorFactory::setInstance($this->processor);
    }
    
    public function tearDown(): void
    {
        TransactionLogFactory::setInstance(null);
        CreditCardProcessorFactory::setInstance(null);
    }
    
    public function testSuccessfulCharge()
    {
        $billingService = new RealBillingService();
        $receipt = $billingService->chargeOrder($this->order, $this->creditCard);

        $this->assertTrue($receipt->hasSuccessfulCharge());
        $this->assertEquals(100, $receipt->getAmountOfCharge());
        $this->assertEquals($creditCard, $processor->getCardOfOnlyCharge());
        $this->assertEquals(100, $processor->getAmountOfOnlyCharge());
        $this->assertTrue($this->transactionLog->wasSuccessLogged());
    }
}
```

This code is clumsy. A global variable holds the mock implementation, so we need
to be careful about setting it up and tearing it down. Should the `tearDown`
fail, the global variable continues to point at our test instance. This could
cause problems for other tests. It also prevents us from running multiple tests
in parallel.

But the biggest problem is that the dependencies are *hidden in the code*. If we
add a dependency on a `CreditCardFraudTracker`, we have to re-run the tests to
find out which ones will break. Should we forget to initialize a factory for a
production service, we don't find out until a charge is attempted. As the
application grows, babysitting factories becomes a growing drain on
productivity.

Quality problems will be caught by QA or acceptance tests. That may be
sufficient, but we can certainly do better.

## Dependency Injection

Like the factory, dependency injection is just a design pattern. The core
principle is to *separate behaviour from dependency resolution*. In our example,
the `RealBillingService` is not responsible for looking up the `TransactionLog`
and `CreditCardProcessor`. Instead, they're passed in as constructor parameters:

```php
public class RealBillingService implements BillingServiceInterface
{
    public function __construct(
        private readonly CreditCardProcessor $processor,
        private readonly TransactionLog $transactionLog
    ) {}
    
    public chargeOrder(PizzaOrder $order, CreditCard $creditCard): Receipt
    {
        try {
            $result = $this->processor->charge($creditCard, $order->getAmount());
            $this->transactionLog->logChargeResult(result);
        
            return $result->wasSuccessful()
                ? Receipt::forSuccessfulCharge($order->getAmount())
                : Receipt::forDeclinedCharge($result->getDeclineMessage());
        } catch (UnreachableException $e) {
            $this->transactionLog->logConnectException($e);

            return Receipt::forSystemFailure($e->getMessage());
        }
    }
}
```

We don't need any factories, and we can simplify the testcase by removing the
`setUp` and `tearDown` boilerplate:

```php
public class RealBillingServiceTest extends TestCase
{
    private PizzaOrder $order;
    private CreditCard $creditCard;
    private InMemoryTransactionLog $transactionLog;
    private FakeCreditCardProcessor $processor;

    public function setUp(): void
    {
        $this->order = new PizzaOrder(100);
        $this->$creditCard = new CreditCard("1234", 11, 2010);
        $this->$transactionLog = new InMemoryTransactionLog();
        $this->$processor = new FakeCreditCardProcessor();      
    }

  public function testSuccessfulCharge()
  {
        $billingService= new RealBillingService($this->processor, $this->transactionLog);
        $receipt = $billingService->chargeOrder($this->order, $this->creditCard);

        $this->assertTrue(receipt.hasSuccessfulCharge());
        $this->assertSame(100, $receipt->getAmountOfCharge());
        $this->assertSame(creditCard, $this->processor->getCardOfOnlyCharge());
        $this->assertSame(100, $this->processor->getAmountOfOnlyCharge());
        $this->assertTrue($this->transactionLog->wasSuccessLogged());
  }
}
```

Now, whenever we add or remove dependencies, the compiler will remind us what
tests need to be fixed. The dependency is *exposed in the API signature*.

Unfortunately, now the clients of `BillingService` need to lookup its
dependencies. We can fix some of these by applying the pattern again! Classes
that depend on it can accept a `BillingService` in their constructor. For
top-level classes, it's useful to have a framework. Otherwise you'll need to
construct dependencies recursively when you need to use a service:

```php
<?php
$processor = new PaypalCreditCardProcessor();
$transactionLog = new DatabaseTransactionLog();
$billingService = new RealBillingService($processor, $transactionLog);
// ...
```

## Dependency Injection with Ray.Di

The dependency injection pattern leads to code that's modular and testable, and
Guice makes it easy to write. To use Guice in our billing example, we first need
to tell it how to map our interfaces to their implementations. This
configuration is done in a Guice module, which is any Java class that implements
the `Module` interface:

```php
public class BillingModule extends AbstractModule
{
    protected function configure(): void
    {
        $this->bind(TransactionLog::class)->to(DatabaseTransactionLog::class);
        $this->bind(CreditCardProcessor::class)->(PaypalCreditCardProcessor::class);
        $this->bind(BillingServiceInterface::class)->(RealBillingService::class);
      }
}
```

Ray.Di will inspect the  constructor, and lookup values for each parameter.

```php
public class RealBillingService implements BillingServiceInterface
{
    public function __construct(
        private readonly CreditCardProcessor $processor,
        private readonly TransactionLog $transactionLog
    ) {}

    public chargeOrder(PizzaOrder $order, CreditCard $creditCard): Receipt
    {
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
```

Finally, we can put it all together. The `Injector` can be used to get an
instance of any of the bound classes.

```php
<?php
$injector = new Injector(new BillingModule());
$billingService = $injector->getInstance(BillingServiceInterface::class);
//...

```

[Getting started](GettingStarted.md) explains how this all works.
