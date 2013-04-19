<?php

namespace Ray\Di\Aop;

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
