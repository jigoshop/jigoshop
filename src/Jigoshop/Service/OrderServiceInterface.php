<?php

namespace Jigoshop\Service;

use Jigoshop\Entity\Cart;
use Jigoshop\Entity\Order;

/**
 * Orders service interface.
 *
 * @package Jigoshop\Service
 * @author Amadeusz Starzykiewicz
 */
interface OrderServiceInterface extends ServiceInterface
{
	/**
	 * Prepares order based on cart.


*
*@param \Jigoshop\Entity\Cart $cart Cart to fetch data from.
	 * @return Order Prepared order.
	 */
	public function createFromCart(Cart $cart);

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

	/**
	 * Saves item meta value to database.
	 *
	 * @param $item Order\Item Item of the meta.
	 * @param $meta Order\Item\Meta Meta to save.
	 */
	public function saveItemMeta($item, $meta);

	/**
	 * Adds a note to the order.
	 *
	 * @param $order \Jigoshop\Entity\Order The order.
	 * @param $note string Note text.
	 * @param $private bool Is note private?
	 * @return int Note ID.
	 */
	public function addNote($order, $note, $private = true);
}
