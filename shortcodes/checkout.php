<?php
/**
 * Checkout shortcode
 *
 * DISCLAIMER
 *
 * Do not edit or add directly to this file if you wish to upgrade Jigoshop to newer
 * versions in the future. If you wish to customise Jigoshop core for your needs,
 * please use our GitHub repository to publish essential changes for consideration.
 *
 * @package             Jigoshop
 * @category            Checkout
 * @author              Jigoshop
 * @copyright           Copyright © 2011-2014 Jigoshop.
 * @license             GNU General Public License v3
 */

function get_jigoshop_checkout( $atts ) {
	return jigoshop_shortcode_wrapper('jigoshop_checkout', $atts);
}

function jigoshop_checkout( $atts ) {
	if (!defined('JIGOSHOP_CHECKOUT')) define('JIGOSHOP_CHECKOUT', true);

	$non_js_checkout = (isset($_POST['update_totals']) && $_POST['update_totals']) ? true : false;

	$result = jigoshop_cart::check_cart_item_stock();

	if (is_wp_error($result)) jigoshop::add_error( $result->get_error_message() );

	if ( ! jigoshop::has_errors() && $non_js_checkout) jigoshop::add_message( __('The order totals have been updated. Please confirm your order by pressing the Place Order button at the bottom of the page.', 'jigoshop') );

	jigoshop::show_messages();

	jigoshop_get_template('checkout/form.php', false);

}

function jigoshop_process_checkout()
{
	if (!is_checkout() || is_jigoshop_single_page(JIGOSHOP_PAY)) {
		return;
	}

	if (count(jigoshop_cart::get_cart()) == 0) {
		wp_safe_redirect(get_permalink(jigoshop_get_page_id('cart')));
		exit;
	}

	/** @var jigoshop_checkout $_checkout */
	$_checkout = jigoshop_checkout::instance();
	$result = $_checkout->process_checkout();

	if(isset($result['result']) && $result['result'] === 'success'){
		wp_safe_redirect(apply_filters('jigoshop_is_ajax_payment_successful', $result['redirect']));
		exit;
	}

	if(isset($result['redirect'])){
		wp_safe_redirect(get_permalink($result['redirect']));
		exit;
	}
}
add_action('template_redirect', 'jigoshop_process_checkout');
