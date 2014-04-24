<?php

namespace Jigoshop\Service;

/**
 * Product service.
 *
 * @package Jigoshop\Service
 * @author Jigoshop
 */
class Product implements ServiceInterface
{
	/**
	 * Finds product specified by ID.
	 *
	 * @param $id int Product ID.
	 * @return \Jigoshop\Product
	 */
	public function find($id)
	{
		// TODO: Implement
		return new \Jigoshop\Product();
	}

	/**
	 * Finds items specified using WordPress query.
	 *
	 * @param $query \WP_Query WordPress query.
	 * @return array Collection of found items.
	 */
	public function findByQuery(\WP_Query $query)
	{
		// TODO: Implement findByQuery() method.
	}
}