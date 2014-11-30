<?php

namespace Jigoshop\Service;

use Jigoshop\Entity\Order;
use Jigoshop\Frontend\Cart;

/**
 * Orders service interface.
 *
 * @package Jigoshop\Service
 * @author Amadeusz Starzykiewicz
 */
interface OrderServiceInterface extends ServiceInterface
{
	/**
	 * Finds item specified by ID.
	 *
	 * @param $id int The ID.
	 * @return Order
	 */
	public function find($id);

	/**
	 * Prepares order based on cart.
	 *
	 * @param Cart $cart Cart to fetch data from.
	 * @return Order Prepared order.
	 */
	public function createFromCart(Cart $cart);

	/**
	 * Finds item for specified WordPress post.
	 *
	 * @param $post \WP_Post WordPress post.
	 * @return Order Item found.
	 */
	public function findForPost($post);

	/**
	 * Finds orders for specified user.
	 *
	 * @param $userId int User ID.
	 * @return array Orders found.
	 */
	public function findForUser($userId);

	/**
	 * @param $month int Month to find orders from.
	 * @param $year int Year to find orders from.
	 * @return array List of orders from selected month.
	 */
	public function findFromMonth($month, $year);

	/**
	 * @return array List of orders that are too long in Pending status.
	 */
	public function findOldPending();

	/**
	 * @return array List of orders that are too long in Processing status.
	 */
	public function findOldProcessing();
}
