<?php

namespace Ray\Di;

use Ray\Aop\Weaver;

// Ray.Aop
require dirname(dirname(__DIR__)) . '/vendor/Ray.Aop/src.php';
require __DIR__ . '/BillingService.php';
require __DIR__ . '/RealBillingService.php';
require __DIR__ . '/SalesTax.php';

$weavedBilling = new Weaver(new RealBillingService, [new SalesTax]);
try {
    list($amount, $unit) = $weavedBilling->chargeOrder();
    echo "{$amount}{$unit} Charged.\n";
} catch (\RuntimeException $e) {
    echo $e->getMessage() . "\n";
    exit(1);
}
