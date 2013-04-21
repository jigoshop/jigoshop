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
 * @copyright           Copyright © 2011-2013 Jigoshop.
 * @license             http://jigoshop.com/license/commercial-edition
 */

function get_jigoshop_checkout( $atts ) {
	return jigoshop_shortcode_wrapper('jigoshop_checkout', $atts);
}

function jigoshop_checkout( $atts ) {

	if (!defined('JIGOSHOP_CHECKOUT')) define('JIGOSHOP_CHECKOUT', true);
	
	jigoshop_cart::get_cart();
	if (sizeof(jigoshop_cart::$cart_contents)==0) :
		wp_redirect(jigoshop_get_page_id('cart'));
		exit;
	endif;

	$non_js_checkout = (isset($_POST['update_totals']) && $_POST['update_totals']) ? true : false;

	$_checkout = jigoshop_checkout::instance();

	$_checkout->process_checkout();

	$result = jigoshop_cart::check_cart_item_stock();

	if (is_wp_error($result)) jigoshop::add_error( $result->get_error_message() );

	if ( ! jigoshop::has_errors() && $non_js_checkout) jigoshop::add_message( __('The order totals have been updated. Please confirm your order by pressing the Place Order button at the bottom of the page.', 'jigoshop') );

	jigoshop::show_messages();

	jigoshop_get_template('checkout/form.php', false);

}
