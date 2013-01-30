<?php

namespace Ray\Di\Tests;

class RealBillingService implements BillingService
{
    /**
     * @SalesTax
     */
    public function chargeOrder()
    {
        return[100, 'yen'];
    }

    public function chargeOrderWithNoTax()
    {
        return[100, 'yen'];
    }
}
