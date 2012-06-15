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
 * 1. Archive 'pending' orders by setting their status to 'on-hold'.
 */

class jigoshop_cron extends Jigoshop_Base {

	function __construct () {

		global $wpdb;
		$this->wpdb = &$wpdb;

		$this->jigoshop_schedule_events();

		add_action('http_request_args'            , array($this , 'no_ssl_http_request_args'), 10, 2);
		add_action( 'jigoshop_cron_pending_orders', array( $this, 'jigoshop_update_pending_orders' ) );
		add_action( 'jigoshop_cron_check_beta'    , array( $this, 'jigoshop_update_beta_init'      ) );
		add_action( 'init'                        , array( $this, 'jigoshop_update_beta_now'       ) );

	}

	function jigoshop_schedule_events() {

		/* Mark old 'pending' orders to 'on-hold' */
		if ( !wp_next_scheduled( 'jigoshop_cron_pending_orders' ) ) {
			wp_schedule_event(time(), 'daily', 'jigoshop_cron_pending_orders' );
		}

		/* Remove scheduled beta checker, and clear the plugin update transient. */
		if ( wp_next_scheduled( 'jigoshop_cron_check_beta' ) && self::get_options()->get_option( 'jigoshop_use_beta_version' ) == 'no' ) {
			delete_site_transient( 'update_plugins' );
			wp_clear_scheduled_hook('jigoshop_cron_check_beta');
		}
		/* Schedule the daily beta checker, and run it now since the user enabled it just now. */
		else if ( !wp_next_scheduled( 'jigoshop_cron_check_beta' ) && self::get_options()->get_option( 'jigoshop_use_beta_version' ) == 'yes' ) {
			$this->jigoshop_update_beta_init();
			wp_schedule_event(time(), 'daily', 'jigoshop_cron_check_beta' );
		}


	}

	function jigoshop_update_pending_orders() {

		$lastMonth = date('Y-m-d', strtotime("-1 months"));

		$orders = $this->wpdb->get_results($this->wpdb->prepare("
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
		"));

		foreach ($orders as $v) :
			$order = new jigoshop_order($v->post_id);
			$order->update_status( 'on-hold', __('Archived due to order being in pending state for a month or longer.', 'jigoshop') );
		endforeach;

	}

	/* Manually invoke the beta updater if the user requests. */
	function jigoshop_update_beta_now() {

		if( !empty($_REQUEST['action']) && $_REQUEST['action'] == 'jigoshop_beta_check' && check_admin_referer('jigoshop_check_beta_'.get_current_user_id().'_wpnonce') && is_super_admin() ) {

			update_option('jigoshop_check_beta_manually', true);
			$this->jigoshop_update_beta_init();

			add_action( 'jigoshop_admin_settings_notices', array ($this, 'jigoshop_beta_check_notice') );

		}

	}

	function jigoshop_beta_check_notice() {
		echo '<div id="message" class="updated"><p>' . __(sprintf("<strong>Checking for beta...</strong> Visit the <a href='%s'>plugin manager</a> to see if an update is available!", admin_url().'plugins.php'), 'jigoshop') . '</strong></p></div>';
	}

	function jigoshop_update_beta_init() {

		/* Clear the site transient so we have a clean cache to start with. */
		delete_site_transient( 'update_plugins' );

		/* Hook into the plugin updater with our beta checker function. */
		add_filter( 'pre_set_site_transient_update_plugins' , array( $this, 'jigoshop_update_beta_checker'   ) );
		add_filter( 'upgrader_post_install', array( $this, 'jigoshop_upgrader_post_install' ), 10, 3 );
	}

	public function jigoshop_upgrader_post_install( $true, $hook_extra, $result ) {

		global $wp_filesystem;

		// Move & Activate
		$proper_dir         = plugin_basename(dirname(dirname(__FILE__)));
		$plugin_slug        = $proper_dir . '/jigoshop.php';
		$proper_destination = WP_PLUGIN_DIR.'/' . $proper_dir;

		$wp_filesystem->move( $result['destination'], $proper_destination );
		$result['destination'] = $proper_destination;
		$activate = activate_plugin( WP_PLUGIN_DIR.'/'.$plugin_slug );

		// Output the update message
		$fail		= __('The plugin has been updated, but could not be reactivated. Please reactivate it manually.', 'jigoshop');
		$success	= __('Plugin reactivated successfully.', 'jigoshop');
		echo is_wp_error( $activate ) ? $fail : $success;
		return $result;

	}

	function no_ssl_http_request_args($args, $url) {

		$args['sslverify'] = false;
		return $args;

	}

	/* Check for Jigoshop beta updates. */
	function jigoshop_update_beta_checker( $transient ) {

		if( self::get_options()->get_option('jigoshop_use_beta_version') == 'no' && self::get_options()->get_option('jigoshop_check_beta_manually') === false )
			return false;

		// Check if the transient contains the 'checked' information
		if( empty( $transient->checked ) )
			return $transient;

		$url     = 'https://raw.github.com/jigoshop/jigoshop/dev/version.txt';
		$dir     = plugin_basename( dirname(dirname(__FILE__)) ) . '/jigoshop.php';
		$request = wp_remote_get( $url, array('sslverify'=> false) );

		if ( is_wp_error( $request ) )
			return false;

		/* The version number. */
		$current = $request['body'];

		/* Do we need to update? */
		if( version_compare($transient->checked[$dir], $current) < 0) {
			$response                  = new stdClass();
			$response->new_version     = $current ;
			$response->slug            = $dir;
			$response->url             = 'https://github.com/jigoshop/jigoshop';
			$response->package         = 'https://github.com/jigoshop/jigoshop/zipball/dev';

			if ( false !== $response )
				$transient->response[ $dir ] = $response;

		}

		delete_option('jigoshop_check_beta_manually');

		return $transient;

	}

}

$jigoshop_cron = new jigoshop_cron();