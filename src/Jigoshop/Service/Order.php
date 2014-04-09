<?php

namespace Jigoshop\Service;

use Jigoshop\Entity\EntityInterface;

/**
 * Orders service.
 *
 * @package Jigoshop\Service
 * @author Jigoshop
 */
class Order implements OrderServiceInterface
{
	/**
	 * Finds order specified by ID.
	 *
	 * @param $id int Order ID.
	 * @return \Jigoshop\Entity\Order
	 */
	public function find($id)
	{
		// TODO: Implement
		return new \Jigoshop\Entity\Order();
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

	/**
	 * Saves order to database.
	 *
	 * @param $object EntityInterface Order to save.
	 * @throws Exception
	 */
	public function save(EntityInterface $object)
	{
		if(!($object instanceof \Jigoshop\Entity\Order))
		{
			throw new Exception('Trying to save not an order!');
		}

//		$fields = $object->getDirtyFields();
//
//		if(in_array('id', $fields) || in_array('name', $fields))
//		{
//			wp_update_post(array(
//				'ID' => $object->getId(),
//				'post_title' => $object->getName(),
//			));
//			unset($fields[array_search('id', $fields)], $fields[array_search('name', $fields)]);
//		}
//
//		foreach($fields as $field)
//		{
//			update_post_meta($object->getId(), $field, $object->get($field));
//		}
	}
}