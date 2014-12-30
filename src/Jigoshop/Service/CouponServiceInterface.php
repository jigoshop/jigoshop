<?php
namespace Jigoshop\Service;


/**
 * Coupon service.
 *
 * @package Jigoshop\Service
 */
interface CouponServiceInterface extends ServiceInterface
{
	/**
	 * @param $code string Code of the coupon to find.
	 * @return \Jigoshop\Entity\Coupon Coupon found.
	 */
	public function findByCode($code);

	/**
	 * @return array List of available coupon types.
	 */
	public function getTypes();

	/**
	 * @param $coupon \Jigoshop\Entity\Coupon
	 * @return string Type name.
	 */
	public function getType($coupon);

	/**
	 * @param array $codes List of codes to find.
	 * @return array Found coupons.
	 */
	public function getByCodes(array $codes);
}
