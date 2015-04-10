<?php
/**
 * Main admin file which loads all settings panels and sets up the menus.
 *
 * DISCLAIMER
 *
 * Do not edit or add directly to this file if you wish to upgrade Jigoshop to newer
 * versions in the future. If you wish to customise Jigoshop core for your needs,
 * please use our GitHub repository to publish essential changes for consideration.
 *
 * @package             Jigoshop
 * @category            Admin
 * @author              Jigoshop
 * @copyright           Copyright © 2011-2014 Jigoshop.
 * @license             GNU General Public License v3
 */

require_once( 'jigoshop-install.php' );
require_once( 'jigoshop-write-panels.php' );
require_once( 'jigoshop-admin-settings-api.php' );
require_once( 'jigoshop-admin-status.php' );
require_once( 'jigoshop-admin-attributes.php' );
require_once( 'jigoshop-admin-post-types.php' );
require_once( 'jigoshop-admin-product-quick-bulk-edit.php' );
require_once( 'jigoshop-admin-taxonomies.php' );
require_once( 'jigoshop-user-profile.php' );

// Contextual help only works for 3.3 due to updated API
if ( get_bloginfo('version') >= '3.3' ) {
	require_once( 'jigoshop-admin-help.php' );
}

add_action('admin_notices', function(){
	if(isset($_GET['jigoshop_message'])){
		switch($_GET['jigoshop_message']){
			case 'invalid_variation_price':
				echo '<div class="error"><p>'.__('<strong>Error!</strong> One of variations has invalid price!', 'jigoshop').'</p></div>';
				break;
		}
	}
});

add_action('admin_notices', 'jigoshop_update');
function jigoshop_update() {
	// Run database upgrade if required
	if ( is_admin() && get_site_option('jigoshop_db_version') < JIGOSHOP_DB_VERSION ) {

		if ( isset($_GET['jigoshop_update_db']) && (bool) $_GET['jigoshop_update_db'] ) {
			require_once( JIGOSHOP_DIR.'/jigoshop_upgrade.php' );
			jigoshop_upgrade();

		} else {

			// Display upgrade nag
			echo '
				<div class="update-nag">
					'.sprintf(__('Your database needs an update for Jigoshop. Please <strong>backup</strong> &amp; %s.', 'jigoshop'), '<a href="' . add_query_arg('jigoshop_update_db', 'true') . '">' . __('update now', 'jigoshop') . '</a>').'
				</div>
			';
		}
	}
}

/**
 * Admin Menus
 *
 * Sets up the admin menus in wordpress.
 *
 * @since 		1.0
 */
add_action('admin_menu', 'jigoshop_before_admin_menu', 9);
function jigoshop_before_admin_menu()
{
	global $menu;

	if (current_user_can('manage_jigoshop')) {
		$menu[54] = array('', 'read', 'separator-jigoshop', '', 'wp-menu-separator jigoshop');
	}

	add_menu_page(__('Jigoshop'), __('Jigoshop'), 'manage_jigoshop', 'jigoshop', 'jigoshop_dashboard', null, 55);
	add_submenu_page('jigoshop', __('Dashboard', 'jigoshop'), __('Dashboard', 'jigoshop'), 'manage_jigoshop', 'jigoshop', 'jigoshop_dashboard');
	add_submenu_page('jigoshop', __('Reports', 'jigoshop'), __('Reports', 'jigoshop'), 'view_jigoshop_reports', 'jigoshop_reports', 'jigoshop_reports');
	add_submenu_page('edit.php?post_type=product', __('Attributes', 'jigoshop'), __('Attributes', 'jigoshop'), 'manage_product_terms', 'jigoshop_attributes', 'jigoshop_attributes');

	do_action('jigoshop_before_admin_menu');
}

add_action('admin_menu', 'jigoshop_after_admin_menu', 50);
function jigoshop_after_admin_menu()
{
	$admin_page = add_submenu_page('jigoshop', __('Settings'), __('Settings'), 'manage_jigoshop', 'jigoshop_settings', array(Jigoshop_Admin_Settings::instance(), 'output_markup'));

	add_action('admin_print_scripts-'.$admin_page, function (){
		do_action('jigoshop_admin_enqueue_scripts');
	});

	add_submenu_page('jigoshop', __('System Information', 'jigoshop'), __('System Info', 'jigoshop'), 'manage_jigoshop', 'jigoshop_system_info', array('Jigoshop_Admin_Status', 'output'));

	do_action('jigoshop_after_admin_menu');
}

function jigoshop_reports()
{
	require_once('jigoshop-admin-reports.php');
	Jigoshop_Admin_Reports::output();
}

function jigoshop_dashboard()
{
	require_once('jigoshop-admin-dashboard.php');
	new jigoshop_dashboard();
}

/**
 * Admin Head
 *
 * Outputs some styles in the admin <head> to show icons on the jigoshop admin pages
 *
 * @since 		1.0
 */
function jigoshop_admin_head() {
	?>
	<style type="text/css">

		<?php if ( isset($_GET['taxonomy']) && $_GET['taxonomy']=='product_cat' ) : ?>
			.icon32-posts-product { background-position: -243px -5px !important; }
		<?php elseif ( isset($_GET['taxonomy']) && $_GET['taxonomy']=='product_tag' ) : ?>
			.icon32-posts-product { background-position: -301px -5px !important; }
		<?php endif; ?>

	</style>
	<?php
}
add_action('admin_head', 'jigoshop_admin_head');

function jigoshop_get_plugin_data( $key = 'Version' ) {
	$data = get_plugin_data( JIGOSHOP_DIR.'/jigoshop.php' );

	return $data[$key];
}

function jigoshop_feature_product() {

	if( !is_admin() ) die;

	if( !current_user_can('edit_posts') ) wp_die( __('You do not have sufficient permissions to access this page.') );

	$post_id = isset($_GET['product_id']) && (int)$_GET['product_id'] ? (int)$_GET['product_id'] : '';

	if(!$post_id) die;

	$post = get_post($post_id);
	if(!$post) die;

	if($post->post_type !== 'product') die;

	$product = new jigoshop_product($post->ID);

	update_post_meta( $post->ID, 'featured', ! $product->is_featured() );

	$sendback = remove_query_arg( array('trashed', 'untrashed', 'deleted', 'ids'), wp_get_referer() );
	wp_redirect( $sendback );
	exit;

}
add_action('wp_ajax_jigoshop-feature-product', 'jigoshop_feature_product');

/**
 * Returns proper post_type
 */
function jigoshop_get_current_post_type() {

	global $post, $typenow, $current_screen;

	if( $current_screen && @$current_screen->post_type ) return $current_screen->post_type;

	if( $typenow ) return $typenow;

	if( !empty($_REQUEST['post_type']) ) return sanitize_key( $_REQUEST['post_type'] );

	if ( !empty($post) && !empty($post->post_type) ) return $post->post_type;

	if( ! empty($_REQUEST['post']) && (int)$_REQUEST['post'] ) {
		$p = get_post( $_REQUEST['post'] );
		return $p ? $p->post_type : '';
	}

	return '';
}

/**
 * Categories ordering
 */

/**
 * Load needed scripts to order categories
 */
function jigoshop_categories_scripts()
{
	if (!isset($_GET['taxonomy']) || $_GET['taxonomy'] !== 'product_cat') {
		return;
	}

	wp_enqueue_script('jquery-ui-sortable');
	jigoshop_add_script('jigoshop-categories-ordering', JIGOSHOP_URL.'/assets/js/categories-ordering.js', array('jquery-ui-sortable'));
}
add_action('admin_footer-edit-tags.php', 'jigoshop_categories_scripts');

/**
 * Ajax request handling for categories ordering
 */
function jigoshop_categories_ordering() {

	global $wpdb;

	$id = (int)$_POST['id'];
	$next_id  = isset($_POST['nextid']) && (int) $_POST['nextid'] ? (int) $_POST['nextid'] : null;

	if( ! $id || ! $term = get_term_by('id', $id, 'product_cat') ) die(0);

	jigoshop_order_categories( $term, $next_id);

	$children = get_terms('product_cat', "child_of=$id&menu_order=ASC&hide_empty=0");
	if( $term && sizeof($children) ) {
		echo 'children';
		die;
	}

}
add_action('wp_ajax_jigoshop-categories-ordering', 'jigoshop_categories_ordering');


if (!function_exists('boolval')) {
	/**
	 * Helper function to get the boolean value of a variable. If not strict, this function will return true
	 * if the variable is not false and not empty. If strict, the value of the variable must exactly match a
	 * value in the true test array to evaluate to true
	 *
	 * @param $in The input variable
	 * @param bool $strict
	 * @return bool|null|string
	 */
	function boolval($in, $strict = false) {
		if (is_bool($in)){
			return $in;
		}
		$in = strtolower($in);
		$out = null;
		if (in_array($in, array('false', 'no', 'n', 'off', '0', 0, null), true)) {
			$out = false;
		} else if ($strict) {
			if (in_array($in, array('true', 'yes', 'y', 'on', '1', 1), true)) {
				$out = true;
			}
		} else {
			$out = ($in ? true : false);
		}
		return $out;
	}
}
