<?php
/**
 * WordPress Cron Tasks
 *
 * DISCLAIMER
 *
 * Do not edit or add directly to this file if you wish to upgrade Jigoshop to newer
 * versions in the future. If you wish to customise Jigoshop core for your needs,
 * please use our GitHub repository to publish essential changes for consideration.
 *
 * @package             Jigoshop
 * @category            Core
 * @author              Jigoshop
 * @copyright           Copyright © 2011-2014 Jigoshop.
 * @license             GNU General Public License v3
 */


class jigoshop_cron extends Jigoshop_Base {


	function __construct () {

		$this->jigoshop_schedule_events();
		add_action( 'jigoshop_cron_pending_orders', array( $this, 'jigoshop_update_pending_orders' ) );
		add_action( 'jigoshop_cron_processing_orders', array( $this, 'jigoshop_complete_processing_orders' ));

	}

	function jigoshop_schedule_events() {
		if ( ! wp_next_scheduled( 'jigoshop_cron_pending_orders' ) ) {
			wp_schedule_event( time(), 'daily', 'jigoshop_cron_pending_orders' );
		}

		if ( ! wp_next_scheduled( 'jigoshop_cron_processing_orders' ) ) {
			wp_schedule_event( time(), 'daily', 'jigoshop_cron_processing_orders' );
		}
	}


	function jigoshop_update_pending_orders() {

		if ( self::get_options()->get( 'jigoshop_reset_pending_orders' ) == 'yes' ) {

			add_filter( 'posts_where', array( $this, 'orders_filter_when' ));

			$orders = get_posts( array(

				'post_status'       => 'publish',
				'post_type'         => 'shop_order',
				'shop_order_status' => 'pending',
				'suppress_filters'  => false,
				'fields'            => 'ids',

			));

			remove_filter( 'posts_where', array( $this, 'orders_filter_when' ));
			jigoshop_emails::suppress_next_action();

			foreach ( $orders as $index => $order_id ) {
				$order = new jigoshop_order( $order_id );
				$order->update_status( 'on-hold', __('Archived due to order being in pending state for a month or longer.', 'jigoshop'));
			}
		}

	}

	function jigoshop_complete_processing_orders() {

		if ( self::get_options()->get( 'jigoshop_complete_processing_orders' ) == 'yes' ) {

			add_filter( 'posts_where', array( $this, 'orders_filter_when' ));

			$orders = get_posts( array(
				'post_status'       => 'publish',
				'post_type'         => 'shop_order',
				'shop_order_status' => 'processing',
				'suppress_filters'  => false,
				'fields'            => 'ids',
			));

			remove_filter( 'posts_where', array( $this, 'orders_filter_when' ));
			jigoshop_emails::suppress_next_actions();

			foreach ( $orders as $index => $order_id ) {
				$order = new jigoshop_order( $order_id );
				$order->update_status( 'completed', __('Completed due to order being in processing state for a month or longer.', 'jigoshop'));
			}

			jigoshop_emails::allow_next_actions();
		}
	}

	function orders_filter_when( $when = '' ) {
		$when .= " AND post_date < '" . date('Y-m-d', strtotime('-30 days')) . "'";
		return $when;
	}
}
