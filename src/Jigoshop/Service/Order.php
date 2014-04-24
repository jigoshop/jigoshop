<?php

namespace Jigoshop\Service;

/**
 * Orders service.
 *
 * @package Jigoshop\Service
 * @author Jigoshop
 */
class Order implements ServiceInterface
{
	/**
	 * Finds order specified by ID.
	 *
	 * @param $id int Order ID.
	 * @return \Jigoshop\Order
	 */
	public function find($id)
	{
		return new \Jigoshop\Order();
	}

	/**
	 * Finds order specified using WordPress query.
	 *
	 * @param $query \WP_Query WordPress query.
	 * @return array Collection of found orders
	 */
	public function findByQuery(\WP_Query $query)
	{
		// TODO: Update query to retrieve only post IDs
		$results = $query->get_posts();
		$that = $this;
		// TODO: Maybe it is good to optimize this to fetch all found orders data at once?
		$orders = array_map(function($order) use ($that){
			return $that->find($order->ID);
		}, $results);

		return $orders;
	}
}