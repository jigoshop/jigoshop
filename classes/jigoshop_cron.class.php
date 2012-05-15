<?php
/**
 * Cron Task
 *
 * DISCLAIMER
 *
 * Do not edit or add directly to this file if you wish to upgrade Jigoshop to newer
 * versions in the future. If you wish to customise Jigoshop core for your needs,
 * please use our GitHub repository to publish essential changes for consideration.
 *
 * @package             Jigoshop
 * @category            Core
 * @author              Jigowatt
 * @copyright           Copyright © 2011-2012 Jigowatt Ltd.
 * @license             http://jigoshop.com/license/commercial-edition
 */

/**
 * Jigoshop cron tasks.
 *
 * 1. Update product sale prices.
 * 2. Archive 'pending' orders by setting their status to 'on-hold'.
 */

class jigoshop_cron {

	function __construct () {

		global $wpdb;
		$this->wpdb = &$wpdb;

		$this->jigoshop_schedule_events();

		add_action( 'jigoshop_cron_sale_products' , array( $this, 'jigoshop_update_sale_prices'    ) );
		add_action( 'jigoshop_cron_pending_orders', array( $this, 'jigoshop_update_pending_orders' ) );

	}

	function jigoshop_schedule_events() {

		/* Update product price if on sale */
		if ( !wp_next_scheduled( 'jigoshop_cron_sale_products' ) )
			wp_schedule_event(time(), 'daily', 'jigoshop_cron_sale_products' );

		/* Mark old 'pending' orders to 'on-hold' */
		if ( !wp_next_scheduled( 'jigoshop_cron_pending_orders' ) )
			wp_schedule_event(time(), 'daily', 'jigoshop_cron_pending_orders' );

	}

	function jigoshop_update_sale_prices() {

		$this->jigoshop_on_sale_products();
		$this->jigoshop_expired_products();

	}

	/* Products still on sale */
	function jigoshop_on_sale_products() {

		$on_sale = $this->wpdb->get_results("
			SELECT post_id FROM {$this->wpdb->postmeta}
			WHERE meta_key = 'sale_price_dates_from'
			AND meta_value < ".strtotime('NOW')."
		");

		if ( !$on_sale )
			return false;

		foreach ($on_sale as $product) :

			$data = unserialize( get_post_meta($product, 'product_data', true) );
			$price = get_post_meta( $product, 'price', true );

			/* Swap the product's price to the sale price */
			if ( $data['sale_price'] && $price !== $data['sale_price'] )
				update_post_meta( $product, 'price', $data['sale_price'] );

		endforeach;

	}

	/* Expired sale products */
	function jigoshop_expired_products() {

		$sale_expired = $this->wpdb->get_results("
			SELECT post_id FROM {$this->wpdb->postmeta}
			WHERE meta_key = 'sale_price_dates_to'
			AND meta_value < ".strtotime('NOW')."
		");

		if ( !$sale_expired )
			return false;

		foreach ( $sale_expired as $product ) :

			$data = unserialize( get_post_meta($product, 'product_data', true) );
			$price = get_post_meta( $product, 'price', true );

			/* Reset the product price */
			if ( $data['regular_price'] && $price !== $data['regular_price'] )
				update_post_meta( $product, 'price', $data['regular_price'] );

			/* Sale has expired - clear the schedule boxes */
			update_post_meta( $product, 'sale_price_dates_from', '' );
			update_post_meta( $product, 'sale_price_dates_to'  , '' );

		endforeach;

	}

	function jigoshop_update_pending_orders() {

		$lastMonth = date('Y-m-d', strtotime("-1 months"));

		$orders = $this->wpdb->get_results("
			SELECT * FROM {$this->wpdb->posts} AS posts

			LEFT JOIN {$this->wpdb->postmeta}           AS meta   ON posts.ID = meta.post_id
			LEFT JOIN {$this->wpdb->term_relationships} AS rel    ON posts.ID = rel.object_ID
			LEFT JOIN {$this->wpdb->term_taxonomy}      AS tax    USING( term_taxonomy_id )
			LEFT JOIN {$this->wpdb->terms}              AS term   USING( term_id )

			WHERE   meta.meta_key       = 'order_data'
			AND     posts.post_type     = 'shop_order'
			AND     posts.post_status   = 'publish'
			AND     posts.post_date     < '{$lastMonth}'
			AND     tax.taxonomy        = 'shop_order_status'
			AND     term.slug           IN ('pending')
		");

		foreach ($orders as $v) :
			$order = new jigoshop_order($v->post_id);
			$order->update_status( 'on-hold', __('Archived due to order being in pending state for one month.', 'jigoshop') );
		endforeach;

	}

}

$jigoshop_cron = new jigoshop_cron();