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
	const PENDING = 'jigoshop-pending';
	const ON_HOLD = 'jigoshop-on-hold';
	const PROCESSING = 'jigoshop-processing';
	const COMPLETED = 'jigoshop-completed';
	const CANCELLED = 'jigoshop-cancelled';
	const REFUNDED = 'jigoshop-refunded';
//			'failed' => __('Failed', 'jigoshop'), /* can be set from PayPal, not currently shown anywhere -JAP- */
//			'denied' => __('Denied', 'jigoshop'), /* can be set from PayPal, not currently shown anywhere -JAP- */
//			'expired' => __('Expired', 'jigoshop'), /* can be set from PayPal, not currently shown anywhere -JAP- */
//			'voided' => __('Voided', 'jigoshop'), /* can be set from PayPal, not currently shown anywhere -JAP- */

	private static $statuses;

	/**
	 * Checks if selected status exists.
	 *
	 * @param $status string Status name.
	 * @return bool Does status exists?
	 */
	public static function exists($status)
	{
		$statuses = self::getStatuses();

		return isset($statuses[$status]);
	}

	/**
	 * @return array List of available order statuses.
	 */
	public static function getStatuses()
	{
		if (self::$statuses === null) {
			// TODO: Replace with WPAL call
			self::$statuses = apply_filters('jigoshop\order\statuses', array(
				Status::PENDING => __('Pending', 'jigoshop'),
				Status::ON_HOLD => __('On-Hold', 'jigoshop'),
				Status::PROCESSING => __('Processing', 'jigoshop'),
				Status::COMPLETED => __('Completed', 'jigoshop'),
				Status::CANCELLED => __('Cancelled', 'jigoshop'),
				Status::REFUNDED => __('Refunded', 'jigoshop'),
			));
		}

		return self::$statuses;
	}

	/**
	 * Returns status name.
	 *
	 * If name is not found - returns given identifier.
	 *
	 * @param $status string Status identifier.
	 * @return string Status name.
	 */
	public static function getName($status)
	{
		if (!self::exists($status)) {
			return $status;
		}

		$statuses = self::getStatuses();
		return $statuses[$status];
	}
}
