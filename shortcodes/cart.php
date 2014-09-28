<?php
/**
 * Cart shortcode
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
function get_jigoshop_cart($atts) {
    return jigoshop_shortcode_wrapper('jigoshop_cart', $atts);
}

function jigoshop_cart($atts)
{
	unset(jigoshop_session::instance()->selected_rate_id);

	// Process Discount Codes
	if (isset($_POST['apply_coupon']) && $_POST['apply_coupon'] && jigoshop::verify_nonce('cart')) {
		$coupon_code = sanitize_title($_POST['coupon_code']);
		jigoshop_cart::add_discount($coupon_code);
	} elseif (isset($_POST['calc_shipping']) && $_POST['calc_shipping'] && jigoshop::verify_nonce('cart')) { // Update Shipping
		unset(jigoshop_session::instance()->chosen_shipping_method_id);
		$country = $_POST['calc_shipping_country'];
		$state = $_POST['calc_shipping_state'];
		$postcode = $_POST['calc_shipping_postcode'];

		if ($postcode && !jigoshop_validation::is_postcode($postcode, $country)) {
			jigoshop::add_error(__('Please enter a valid postcode/ZIP.', 'jigoshop'));
			$postcode = '';
		} elseif ($postcode) {
			$postcode = jigoshop_validation::format_postcode($postcode, $country);
		}

		if ($country) { // Update customer location
			jigoshop_customer::set_location($country, $state, $postcode);
			jigoshop_customer::set_shipping_location($country, $state, $postcode);

			jigoshop::add_message(__('Shipping costs updated.', 'jigoshop'));
		} else {
			jigoshop_customer::set_shipping_location('', '', '');
			jigoshop::add_message(__('Shipping costs updated.', 'jigoshop'));
		}
	} elseif (isset($_POST['shipping_rates'])) {
		$rates_params = explode(":", $_POST['shipping_rates']);
		$available_methods = jigoshop_shipping::get_available_shipping_methods();
		$shipping_method = $available_methods[$rates_params[0]];

		if ($rates_params[1] != null) {
			jigoshop_session::instance()->selected_rate_id = $rates_params[1];
		}

		$shipping_method->choose(); // chooses the method selected by user.
	}

	// Re-Calc prices. This needs to happen every time the cart page is loaded and after checking post results.
	jigoshop_cart::calculate_totals();

	$result = jigoshop_cart::check_cart_item_stock();
	if (is_wp_error($result)) {
		jigoshop::add_error($result->get_error_message());
	}

	jigoshop_render('shortcode/cart', array(
		'cart' => jigoshop_cart::get_cart(),
		'coupons' => jigoshop_cart::get_coupons(),
	));
}
