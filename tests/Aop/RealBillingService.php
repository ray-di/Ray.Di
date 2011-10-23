<?php

namespace Ray\Di;

/**
 * @Prototype("Singleton")
 * @Aspect
 */
class RealBillingService implements BillingService {

	/**
	 * @SalesTax
	 */
	public function chargeOrder()
	{
	    return array(100, "yen");
	}
}