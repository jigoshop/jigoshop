<?php

use Jigoshop\Frontend\Pages;

function jigowatt_clean($var)
{
	return strip_tags(stripslashes(trim($var)));
}

function jigoshop_price($price)
{
	return \Jigoshop\Helper\Product::formatPrice($price);
}

function jigoshop_is_minumum_version($version)
{
	return jigoshop_is_minimum_version($version);
}

function jigoshop_is_minimum_version($version)
{
	return \Jigoshop\isMinimumVersion($version);
}

function jigoshop_add_required_version_notice($source, $version)
{
	return \Jigoshop\addRequiredVersionNotice($source, $version);
}

function jigoshop_add_script($handle, $src, array $dependencies = array(), array $options = array())
{
	$scripts = \Jigoshop\Integration::getScripts();
	$scripts->add($handle, $src, $dependencies, $options);
}

function jigoshop_remove_script($handle, array $options = array())
{
	$scripts = \Jigoshop\Integration::getScripts();
	$scripts->remove($handle, $options);
}

function jigoshop_localize_script($handle, $object, array $values)
{
	$scripts = \Jigoshop\Integration::getScripts();
	$scripts->localize($handle, $object, $values);
}

function jigoshop_add_style($handle, $src, array $dependencies = array(), array $options = array())
{
	$styles = \Jigoshop\Integration::getStyles();
	$styles->add($handle, $src, $dependencies, $options);
}

function jigoshop_remove_style($handle, array $options = array())
{
	$styles = \Jigoshop\Integration::getStyles();
	$styles->remove($handle, $options);
}

function jigoshop_get_page_id($page)
{
	$options = \Jigoshop\Integration::getOptions();

	if ($page == 'pay') {
		$page = 'checkout';

		add_filter('jigoshop_get_return_url', function() use ($options) {
			$order = \Jigoshop\Integration::getCurrentOrder();

			$link = \Jigoshop\Helper\Api::getEndpointUrl('pay', $order->getId(), get_permalink($options->get('advanced.pages.checkout')));
			$link = add_query_arg(array('receipt' => $order->getPaymentMethod()->getId()), $link);

			return $link;
		}, 9999);
	}

	return $options->get('advanced.pages.'.$page);
}

function jigoshop_get_image_size($size)
{
	if (is_array($size)) {
		return $size;
	}

	$options = \Jigoshop\Integration::getOptions();

	switch ($size) {
		case 'admin_product_list':
			return array(32, 32);
			break;
		case 'shop_tiny':
			$image_size = $options->get('products.images.tiny');
			break;
		case 'shop_thumbnail':
			$image_size = $options->get('products.images.thumbnail');
			break;
		case 'shop_large':
			$image_size = $options->get('products.images.large');
			break;
		case 'shop_small':
		default:
			$image_size = $options->get('products.images.small');
			break;
	}

	return array($image_size['width'], $image_size['height']);
}

function jigoshop_disable_autosave($src, $handle)
{
	if ('autosave' != $handle) {
		return $src;
	}

	return '';
}

function get_jigoshop_currency_symbol()
{
	$currency = \Jigoshop\Helper\Currency::code();
	$currency_symbol = \Jigoshop\Helper\Currency::symbol();

	return apply_filters('jigoshop_currency_symbol', $currency_symbol, $currency);
}


/**
 * Evaluates to true only on the Shop page, not Product categories and tags
 * Note:is used to replace is_page( jigoshop_get_page_id( 'shop' ) )
 *
 * @return bool
 * @since 0.9.9
 */
function is_shop()
{
	return Pages::isShop();
}

/**
 * Evaluates to true only on the Single Product Page
 *
 * @return bool
 * @since 0.9.9
 */
function is_product()
{
	return Pages::isProduct();
}

/**
 * Evaluates to true only on Shop, Product Category, and Product Tag pages
 *
 * @return bool
 * @since 0.9.9
 */
function is_product_list()
{
	return Pages::isProductList();
}

/**
 * Evaluates to true only on the Category Pages
 *
 * @return bool
 * @since 0.9.9
 */
function is_product_category()
{
	return Pages::isProductCategory();
}

/**
 * Evaluates to true only on the Tag Pages
 *
 * @return bool
 * @since 0.9.9
 */
function is_product_tag()
{
	return Pages::isProductTag();
}

/**
 * Evaluates to true only on the Order Tracking page
 *
 * @return bool
 * @since 0.9.9.1
 */
function is_order_tracker()
{
	return Pages::isOrderTracker();
}

/**
 * Evaluates to true only on the Cart page
 *
 * @return bool
 * @since 0.9.8
 */
function is_cart()
{
	return Pages::isCart();
}

/**
 * Evaluates to true only on the Checkout or Pay pages
 *
 * @return bool
 * @since 0.9.8
 */
function is_checkout()
{
	return Pages::isCheckout();
}

/**
 * Evaluates to true only on the main Account or any sub-account pages
 *
 * @return bool
 * @since 0.9.9.1
 */
function is_account()
{
	return Pages::isAccount();
}

/**
 * Evaluates to true for all Jigoshop pages
 *
 * @return bool
 * @since 0.9.9
 */
function is_jigoshop()
{
	return Pages::isJigoshop();
}
