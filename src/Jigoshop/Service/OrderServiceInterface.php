<?php

namespace Jigoshop\Service;

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
	 * Finds item for specified WordPress post.
	 *
	 * @param $post \WP_Post WordPress post.
	 * @return Order Item found.
	 */
	public function findForPost($post);

	/**
	 * @param $month int Month to find orders from.
	 * @return array List of orders from selected month.
	 */
	public function findFromMonth($month);

	/**
	 * @return array List of orders that are too long in Pending status.
	 */
	public function findOldPending();

	/**
	 * @return array List of orders that are too long in Processing status.
	 */
	public function findOldProcessing();
}