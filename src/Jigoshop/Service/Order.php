<?php

namespace Jigoshop\Service;

use Jigoshop\Entity\EntityInterface;
use WPAL\Wordpress;

/**
 * Orders service.
 *
 * @package Jigoshop\Service
 * @author Amadeusz Starzykiewicz
 */
class Order implements OrderServiceInterface
{
	/** @var \WPAL\Wordpress */
	private $wordpress;

	public function __construct(Wordpress $wordpress)
	{
		$this->wordpress = $wordpress;
	}
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
	 * TODO: Replace \WP_Query in order to make Jigoshop testable
	 * @param $query \WP_Query WordPress query.
	 * @return array Collection of found orders
	 */
	public function findByQuery(\WP_Query $query)
	{
		// Fetch only IDs
		$query->query_vars['fields'] = 'ids';
		$results = $query->get_posts();
		$that = $this;
		// TODO: Maybe it is good to optimize this to fetch all found orders data at once?
		$orders = array_map(function($order) use ($that){
			return $that->find($order);
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

	/**
	 * @param $month int Month to find orders from.
	 * @return array List of orders from selected month.
	 */
	public function findFromMonth($month)
	{
//		function orders_this_month( $where = '' ) {
//			global $current_month_offset;
//
//			$month = $current_month_offset;
//			$year = (int) date('Y');
//
//			$first_day = strtotime("{$year}-{$month}-01");
//			$last_day = strtotime('-1 second', strtotime('+1 month', $first_day));
//
//			$after = date('Y-m-d H:i:s', $first_day);
//			$before = date('Y-m-d H:i:s', $last_day);
//
//			$where .= " AND post_date >= '$after'";
//			$where .= " AND post_date <= '$before'";
//
//			return $where;
//		}
//		add_filter( 'posts_where', 'orders_this_month' );
//
//		$args = array(
//			'numberposts'     => -1,
//			'orderby'         => 'post_date',
//			'order'           => 'DESC',
//			'post_type'       => 'shop_order',
//			'post_status'     => 'publish' ,
//			'suppress_filters'=> false
//		);
//		$orders = get_posts( $args );
		// TODO: Implement findFromMonth() method.
		return array();
	}
}