<?php
/**
 * Orders Class
 *
 * The JigoShop orders class loads orders and calculates counts
 *
 * DISCLAIMER
 *
 * Do not edit or add directly to this file if you wish to upgrade Jigoshop to newer
 * versions in the future. If you wish to customise Jigoshop core for your needs,
 * please use our GitHub repository to publish essential changes for consideration.
 *
 * @package             Jigoshop
 * @category            Customer
 * @author              Jigoshop
 * @copyright           Copyright © 2011-2013 Jigoshop.
 * @license             http://jigoshop.com/license/commercial-edition
 */
class jigoshop_orders extends Jigoshop_Base {

	public $orders;
	public $count;
	public $completed_count;
	public $pending_count;
	public $cancelled_count;
	public $on_hold_count;
	public $processing_count;
	public $refunded_count;

	/** Loads orders and counts them */
	function jigoshop_orders() {
		$this->orders = array();

		// Get Counts
		$this->pending_count 	= get_term_by( 'slug', 'pending', 'shop_order_status' )->count;
		$this->completed_count  = get_term_by( 'slug', 'completed', 'shop_order_status' )->count;
		$this->cancelled_count  = get_term_by( 'slug', 'cancelled', 'shop_order_status' )->count;
		$this->on_hold_count    = get_term_by( 'slug', 'on-hold', 'shop_order_status' )->count;
		$this->refunded_count 	= get_term_by( 'slug', 'refunded', 'shop_order_status' )->count;
		$this->processing_count = get_term_by( 'slug', 'processing', 'shop_order_status' )->count;
		$this->count			= wp_count_posts( 'shop_order' )->publish;
	}

	/**
	 * Loads a customers orders
	 *
	 * @param   int		$user_id	ID of the user to load the orders for
	 * @param   int		$limit		How many orders to load
	 */
	function get_customer_orders( $user_id, $limit = 5 ) {

		$args = array(
		    'numberposts'     => $limit,
		    'meta_key'        => 'customer_user',
		    'meta_value'	  => $user_id,
		    'post_type'       => 'shop_order',
		    'post_status'     => 'publish'
		);

		$results = get_posts($args);

		if ($results) :
			foreach ($results as $result) :
				$order = new jigoshop_order();
				$order->populate($result);
				$this->orders[] = $order;
			endforeach;
		endif;
	}
}
