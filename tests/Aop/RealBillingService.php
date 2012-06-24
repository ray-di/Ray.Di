<?php

namespace Ray\Di\Tests;

use Ray\Di\Tests\SalesTax;

class RealBillingService implements BillingService
{
    /**
     * @SalesTax
     */
    public function chargeOrder()
    {
        return array(100, "yen");
    }

    public function chargeOrderWithNoTax()
    {
        return array(100, "yen");
    }
}
