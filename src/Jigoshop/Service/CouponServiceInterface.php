<?php
namespace Jigoshop\Service;

use Jigoshop\Entity\Coupon;


/**
 * Coupon service.
 * TODO: Add caching.
 *
 * @package Jigoshop\Service
 */
interface CouponServiceInterface extends ServiceInterface
{
	/**
	 * Finds item specified by ID.
	 *
	 * @param $id int The ID.
	 * @return Coupon
	 */
	public function find($id);

	/**
	 * Finds item for specified WordPress post.
	 *
	 * @param $post \WP_Post WordPress post.
	 * @return Coupon Item found.
	 */
	public function findForPost($post);

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
