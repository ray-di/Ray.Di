<?php

namespace Ray\Di;

interface BillingService {

	/**
	 * @var Receipt
	 *
	 * @WeekendBlock
	 */
	public function chargeOrder();
}