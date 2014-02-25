<?php

namespace Ray\Di\Aop;

interface BillingService
{
    /**
     * @WeekendBlock
     */
    public function chargeOrder();
}
