<?php

namespace Ray\Di\Tests;

use Ray\Di\Tests\SalesTax;

class AnnotateTaxBilling implements BillingService
{
    /**
     * @SalesTax(0.1)
     */
    public function chargeOrder()
    {
        return [100, "yen"];
    }
}
