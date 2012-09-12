<?php

namespace Ray\Di\Tests;

interface BillingService
{
    /**
     * @var Receipt
     *
     * @WeekendBlock
     */
    public function chargeOrder();
}
