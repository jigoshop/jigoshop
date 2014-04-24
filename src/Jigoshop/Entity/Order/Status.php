<?php

namespace Jigoshop\Entity\Order;

/**
 * Order statuses.
 *
 * @package Jigoshop\Entity\Order
 * @author Amadeusz Starzykiewicz
 */
class Status
{
	const PENDING = 'pending';
	const ON_HOLD = 'on-hold';
	const PROCESSING = 'processing';
	const COMPLETED = 'completed';
	const CANCELLED = 'cancelled';
	const REFUNDED = 'refunded';
//			'failed' => __('Failed', 'jigoshop'), /* can be set from PayPal, not currently shown anywhere -JAP- */
//			'denied' => __('Denied', 'jigoshop'), /* can be set from PayPal, not currently shown anywhere -JAP- */
//			'expired' => __('Expired', 'jigoshop'), /* can be set from PayPal, not currently shown anywhere -JAP- */
//			'voided' => __('Voided', 'jigoshop'), /* can be set from PayPal, not currently shown anywhere -JAP- */
}