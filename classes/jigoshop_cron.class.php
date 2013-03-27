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
 * @author              Jigoshop
 * @copyright           Copyright © 2011-2013 Jigoshop.
 * @license             http://jigoshop.com/license/commercial-edition
 */

/**
 * Jigoshop cron tasks.
 *
 * 1. Archive 'pending' orders by setting their status to 'on-hold'.
 * 2. Perform jigoshop beta version availability checks for auto-update.
 */

class jigoshop_cron extends Jigoshop_Base {

	function __construct () {

		global $wpdb;
		$this->wpdb = &$wpdb;

		$this->jigoshop_schedule_events();

		add_action( 'jigoshop_cron_pending_orders', array( $this, 'jigoshop_update_pending_orders' ) );

	}

	function jigoshop_schedule_events() {

		/* Mark old 'pending' orders to 'on-hold' */
		if ( !wp_next_scheduled( 'jigoshop_cron_pending_orders' ) ) {
			wp_schedule_event( time(), 'daily', 'jigoshop_cron_pending_orders' );
		}

	}

	function jigoshop_update_pending_orders() {

		if ( self::get_options()->get_option( 'jigoshop_reset_pending_orders' ) == 'yes' ) {
			$lastMonth = date('Y-m-d', strtotime("-1 months"));
	
			$orders = $this->wpdb->get_results(
				"SELECT * FROM {$this->wpdb->posts} AS posts
	
				LEFT JOIN {$this->wpdb->postmeta}           AS meta   ON posts.ID = meta.post_id
				LEFT JOIN {$this->wpdb->term_relationships} AS rel    ON posts.ID = rel.object_ID
				LEFT JOIN {$this->wpdb->term_taxonomy}      AS tax    USING( term_taxonomy_id )
				LEFT JOIN {$this->wpdb->terms}              AS term   USING( term_id )
	
				WHERE   meta.meta_key       = 'order_data'
				AND     posts.post_type     = 'shop_order'
				AND     posts.post_status   = 'publish'
				AND     posts.post_date     < '{$lastMonth}'
				AND     tax.taxonomy        = 'shop_order_status'
				AND     term.slug           IN ('pending');"
			);

			remove_action('order_status_pending_to_on-hold', 'jigoshop_processing_order_customer_notification');
			foreach ($orders as $v) :
				$order = new jigoshop_order($v->post_id);
				$order->update_status( 'on-hold', __('Archived due to order being in pending state for a month or longer.', 'jigoshop') );
			endforeach;
			add_action('order_status_pending_to_on-hold', 'jigoshop_processing_order_customer_notification');
		}

	}

}
