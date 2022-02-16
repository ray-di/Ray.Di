# Getting Started

## Creating Object graph

With dependency injection, objects accept dependencies in their constructors. To construct an object, you first build its dependencies. But to build each dependency, you need its dependencies, and so on. So when you build an object, you really need to build an object graph.

Building object graphs by hand is labour intensive, error prone, and makes testing difficult. Instead, Ray.Di can build the object graph for you. But first, Ray.Di needs to be configured to build the graph exactly as you want it.

To illustrate, we'll start the BillingService class that accepts its dependent interfaces in its constructor: ProcessorInterface and LoggerInterface.

```php
class BillingService
{
    public function __construct(
        private readonly ProcessorInterface $processor,
        private readonly LoggerInterface $logger
    ){}
}
```

Ray.Di uses bindings to map types to their implementations. A module is a collection of bindings specified using fluent, English-like method calls:

```php
class BillingModule extends AbstractModule
{
    protected function configure(): void
    {
        $this->bind(ProcessorInterface::class)->to(PaypalProcessor::class); 
        $this->bind(LoggerInterface::class)->to(DatabaseLogger::class);
    }
}
```

The modules are the building blocks of an injector, which is Ray.Di's object-graph builder. First we create the injector, and then we can use that to build the BillingService:

```php
$injector = new Injector(new BillingModule);
$billingService = $injector->getInstance(BillingService::class);
```

By building the billingService, we've constructed a small object graph using Ray.Di.