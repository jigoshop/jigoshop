<?php

namespace Jigoshop\Payment;

use Jigoshop\Entity\Order;
use Jigoshop\Shipping\Method;

/**
 * Interface for external payment methods.
 *
 * Used for methods which returns values after redirection or calls method externally.
 *
 * @package Jigoshop\Payment
 */
interface ExternalMethod extends Method
{
	/**
	 * Processes returned data.
	 *
	 * @return bool Whether processing is successful.
	 */
	public function processResult();
}
