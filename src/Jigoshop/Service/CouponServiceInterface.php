<?php
namespace Jigoshop\Service;


/**
 * Coupon service.
 * TODO: Add caching.
 *
 * @package Jigoshop\Service
 */
interface CouponServiceInterface extends ServiceInterface
{
	/**
	 * @return array List of available coupon types.
	 */
	public function getTypes();

	/**
	 * @param $coupon \Jigoshop\Entity\Coupon
	 * @return string Type name.
	 */
	public function getType($coupon);
}
