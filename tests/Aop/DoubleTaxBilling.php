<?php

namespace Ray\Di;

use Ray\Di\SalesTax;
use Ray\Di\Di\ImplementedBy;

/**
 * @Aspect
 */
class DaubleTaxBilling implements BillingService
{
	/**
	 * @SalesTax(10)
	 * @SalesTax(5)
	 */
	public function chargeOrder()
	{
	    return array(100, "yen");
	}
}