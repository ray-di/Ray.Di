<?php

namespace Ray\Di;

use Ray\Di\SalesTax;
use Ray\Di\Di\ImplementedBy;

/**
 * @Aspect
 */
class RealBillingService implements BillingService {

	/**
	 * @SalesTax(5)
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