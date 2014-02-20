<?php

namespace Ray\Di\Aop;

class CacheBilling implements BillingService
{
    /**
     * @SalesTax
     */
    public function chargeOrder()
    {
        return[100, 'yen'];
    }
}
