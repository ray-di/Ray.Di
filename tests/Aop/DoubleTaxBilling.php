<?php

namespace Ray\Di\Tests;

use Ray\Di\Tests\SalesTax;

use Ray\Di\Di\ImplementedBy;

class DoubleTaxBilling implements BillingService
{
	/**
	 * @SalesTax(0.1)
	 */
	public function chargeOrder()
	{
	    return array(100, "yen");
	}
}