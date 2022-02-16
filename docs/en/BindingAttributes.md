## Binding Attributes

Occasionally you'll want multiple bindings for a same type. For example, you might want both a PayPal credit card processor and a Google Checkout processor.
To enable this, bindings support an optional binding attribute. The attribute and type together uniquely identify a binding. This pair is called a key.

### Defining binding attributes

Define qualifier attribute first. It needs to be annotated with `Qualifier` attribute.

```php
use Ray\Di\Di\Qualifier;

#[Attribute, Qualifier]
final class PayPal
{
}
```

To depend on the annotated binding, apply the attribute to the injected parameter:

```php
public function __construct(
    #[Paypal] private readonly CreditCardProcessorInterface $processor
){}
```
You can specify parameter name with qualifier. Qualifier applied all parameters without it.

```php
public function __construct(
    #[Paypal('processor')] private readonly CreditCardProcessorInterface $processor
){}
```
Lastly we create a binding that uses the attribute. This uses the optional `annotatedWith` clause in the bind() statement:

```php
$this->bind(CreditCardProcessorInterface::class)
  ->annotatedWith(PayPal::class)
  ->to(PayPalCreditCardProcessor::class);
```

### Binding Attributes in Setters

In order to make your custom `Qualifier` attribute inject dependencies by default in any method the
attribute is added, you need to implement the `Ray\Di\Di\InjectInterface`:

```php
use Ray\Di\Di\InjectInterface;
use Ray\Di\Di\Qualifier;

#[Attribute, Qualifier]
final class PaymentProcessorInject implements InjectInterface
{
    public function isOptional()
    {
        return $this->optional;
    }
    
    public function __construct(
        public readonly bool $optional = true
        public readonly string $type;
    ){}
}
```

The interface requires that you implement the `isOptional()` method. It will be used to determine whether
or not the injection should be performed based on whether there is a known binding for it.

Now that you have created your custom injector attribute, you can use it on any method.

```php
#[PaymentProcessorInject(type: 'paypal')]
public setPaymentProcessor(CreditCardProcessorInterface $processor)
{
 ....
}
```

Finally, you can bind the interface to an implementation by using your new annotated information:

```php
$this->bind(CreditCardProcessorInterface::class)
    ->annotatedWith(PaymentProcessorInject::class)
    ->toProvider(PaymentProcessorProvider::class);
```

The provider can now use the information supplied in the qualifier attribute in order to instantiate
the most appropriate class.

## Qualifier

The most common use of a Qualifier attribute is tagging arguments in a function with a certain label,
the label can be used in the bindings in order to select the right class to be instantiated. For those
cases, Ray.Di comes with a built-in binding attribute `#[Named]` that takes a string.

```php
use Ray\Di\Di\Inject;
use Ray\Di\Di\Named;

public function __construct(
    #[Named('checkout')] private CreditCardProcessorInterface $processor
){}
```

To bind a specific name, pass that string using the `annotatedWith()` method.

```php
$this->bind(CreditCardProcessorInterface::class)
    ->annotatedWith('checkout')
    ->to(CheckoutCreditCardProcessor::class);
```

You need to put the `#[Named]` attribuet in order to specify the parameter.

```php
use Ray\Di\Di\Inject;
use Ray\Di\Di\Named;

public function __construct(
    #[Named('checkout')] private CreditCardProcessorInterface $processor,
    #[Named('backup')] private CreditCardProcessorInterface $subProcessor
){}
```

## Annotation / Attribute

Ray.Di can be used either with [doctrine/annotation](https://github.com/doctrine/annotations) in PHP 7/8 or with an [Attributes](https://www.php.net/manual/en/language.attributes.overview.php) in PHP8.
See the annotation code examples in the older [README(v2.10)](https://github.com/ray-di/Ray.Di/tree/2.10.5/README.md).
To make forward-compatible annotations for attributes, see [Custom Annotation Classes](https://github.com/kerveros12v/sacinta4/blob/e976c143b3b7d42497334e76c00fdf38717af98e/vendor/doctrine/annotations/docs/en/custom.rst#optional-constructors-with-named-parameters).
