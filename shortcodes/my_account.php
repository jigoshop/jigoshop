<?php
/**
 * My Account shortcode
 * DISCLAIMER
 * Do not edit or add directly to this file if you wish to upgrade Jigoshop to newer
 * versions in the future. If you wish to customise Jigoshop core for your needs,
 * please use our GitHub repository to publish essential changes for consideration.
 *
 * @package             Jigoshop
 * @category            Customer
 * @author              Jigoshop
 * @copyright           Copyright © 2011-2014 Jigoshop.
 * @license             GNU General Public License v3
 */

function get_jigoshop_my_account($attributes)
{
	return jigoshop_shortcode_wrapper('jigoshop_my_account', $attributes);
}

function jigoshop_my_account($attributes)
{
	global $current_user;
	$options = Jigoshop_Base::get_options();

	$attributes = shortcode_atts(array(
		'recent_orders' => 5
	), $attributes);

	$recent_orders = ('all' == $attributes['recent_orders']) ? -1 : $attributes['recent_orders'];
	get_currentuserinfo();

	jigoshop_render('shortcode/my_account/my_account', array(
		'current_user' => $current_user,
		'options' => $options,
		'recent_orders' => $recent_orders,
	));
}

function get_jigoshop_edit_address()
{
	return jigoshop_shortcode_wrapper('jigoshop_edit_address');
}

function jigoshop_get_address_to_edit()
{
	$address = 'billing';
	if (isset($_GET['address']) && in_array($_GET['address'], array('billing', 'shipping'))) {
		$address = $_GET['address'];
	}

	return $address;
}

function jigoshop_get_address_fields($load_address, $user_id)
{
	$address = array(
		array(
			'name' => $load_address.'_first_name',
			'label' => __('First Name', 'jigoshop'),
			'placeholder' => __('First Name', 'jigoshop'),
			'required' => true,
			'class' => array('form-row-first'),
			'value' => get_user_meta($user_id, $load_address.'_first_name', true)
		),
		array(
			'name' => $load_address.'_last_name',
			'label' => __('Last Name', 'jigoshop'),
			'placeholder' => __('Last Name', 'jigoshop'),
			'required' => true,
			'class' => array('form-row-last columned'),
			'value' => get_user_meta($user_id, $load_address.'_last_name', true)
		),
		array(
			'name' => $load_address.'_company',
			'label' => __('Company', 'jigoshop'),
			'placeholder' => __('Company', 'jigoshop'),
			'class' => array('columned full-row clear'),
			'value' => get_user_meta($user_id, $load_address.'_company_name', true)
		),
		array(
			'name' => $load_address.'_address_1',
			'label' => __('Address', 'jigoshop'),
			'placeholder' => __('Address 1', 'jigoshop'),
			'required' => true,
			'class' => array('form-row-first'),
			'value' => get_user_meta($user_id, $load_address.'_address_1', true)
		),
		array(
			'name' => $load_address.'_address_2',
			'label' => __('Address 2', 'jigoshop'),
			'placeholder' => __('Address 2', 'jigoshop'),
			'class' => array('form-row-last'),
			'label_class' => array('hidden'),
			'value' => get_user_meta($user_id, $load_address.'_address_2', true)
		),
		array(
			'name' => $load_address.'_city',
			'label' => __('City', 'jigoshop'),
			'placeholder' => __('City', 'jigoshop'),
			'required' => true,
			'class' => array('form-row-first'),
			'value' => get_user_meta($user_id, $load_address.'_city', true)
		),
		array(
			'type' => 'postcode',
			'validate' => 'postcode',
			'format' => 'postcode',
			'name' => $load_address.'_postcode',
			'label' => __('Postcode', 'jigoshop'),
			'placeholder' => __('Postcode', 'jigoshop'),
			'required' => true,
			'class' => array('form-row-last'),
			'value' => get_user_meta($user_id, $load_address.'_postcode', true)
		),
		array(
			'type' => 'country',
			'name' => $load_address.'_country',
			'label' => __('Country', 'jigoshop'),
			'required' => true,
			'class' => array('form-row-first'),
			'rel' => $load_address.'_state',
			'value' => get_user_meta($user_id, $load_address.'_country', true)
		),
		array(
			'type' => 'state',
			'name' => $load_address.'_state',
			'label' => __('State/Province', 'jigoshop'),
			'required' => true,
			'class' => array('form-row-last'),
			'rel' => $load_address.'_country',
			'value' => get_user_meta($user_id, $load_address.'_state', true)
		),
		array(
			'name' => $load_address.'_email',
			'validate' => 'email',
			'label' => __('Email Address', 'jigoshop'),
			'placeholder' => __('you@yourdomain.com', 'jigoshop'),
			'required' => true,
			'class' => array('form-row-first'),
			'value' => get_user_meta($user_id, $load_address.'_email', true)
		),
		array(
			'name' => $load_address.'_phone',
			'validate' => 'phone',
			'label' => __('Phone', 'jigoshop'),
			'placeholder' => __('Phone number', 'jigoshop'),
			'required' => true,
			'class' => array('form-row-last'),
			'value' => get_user_meta($user_id, $load_address.'_phone', true)
		)
	);

	return apply_filters('jigoshop_customer_account_address_fields', $address);
}

function jigoshop_edit_address()
{
	$account_url = get_permalink(jigoshop_get_page_id(JIGOSHOP_MY_ACCOUNT));
	$user_id = get_current_user_id();
	$load_address = jigoshop_get_address_to_edit();
	$address = jigoshop_get_address_fields($load_address, $user_id);

	if (isset($_POST['save_address']) && jigoshop::verify_nonce(JIGOSHOP_EDIT_ADDRESS)) {
		if ($user_id > 0) {
			foreach ($address as &$field) {
				if (isset($_POST[$field['name']])) {
					$field['value'] = jigowatt_clean($_POST[$field['name']]);
					update_user_meta($user_id, $field['name'], $field['value']);
				}
			}

			do_action('jigoshop_user_edit_address', $user_id, $address);
		}
	}

	jigoshop_render('shortcode/my_account/edit_address', array(
		'url' => add_query_arg('address', $load_address,
			apply_filters('jigoshop_get_edit_address_page_id', get_permalink(jigoshop_get_page_id(JIGOSHOP_EDIT_ADDRESS)))),
		'account_url' => $account_url,
		'load_address' => $load_address,
		'address' => $address,
	));
}

function get_jigoshop_change_password()
{
	return jigoshop_shortcode_wrapper('jigoshop_change_password');
}

function jigoshop_change_password()
{
	jigoshop_render('shortcode/my_account/change_password', array());
}

function get_jigoshop_view_order()
{
	return jigoshop_shortcode_wrapper('jigoshop_view_order');
}

function jigoshop_view_order()
{
	$options = Jigoshop_Base::get_options();
	$order = new jigoshop_order($_GET['order']);

	jigoshop_render('shortcode/my_account/view_order', array(
		'order' => $order,
		'options' => $options,
	));
}

add_action('template_redirect', function (){
	$isViewOrder = is_jigoshop_single_page(JIGOSHOP_VIEW_ORDER);
	$isEditAddress = is_jigoshop_single_page(JIGOSHOP_EDIT_ADDRESS);
	$isChangePassword = is_jigoshop_single_page(JIGOSHOP_CHANGE_PASSWORD);

	if (($isViewOrder || $isEditAddress || $isChangePassword) && !is_user_logged_in()) {
		wp_safe_redirect(apply_filters('jigoshop_get_myaccount_page_id', get_permalink(jigoshop_get_page_id(JIGOSHOP_MY_ACCOUNT))));
		exit;
	}

	if ($isViewOrder) {
		if (!isset($_GET['order'])) {
			wp_safe_redirect(apply_filters('jigoshop_get_myaccount_page_id', get_permalink(jigoshop_get_page_id('myaccount'))));
			exit;
		}
		$order = new jigoshop_order($_GET['order']);

		if ($order->user_id != get_current_user_id()) {
			wp_safe_redirect(apply_filters('jigoshop_get_myaccount_page_id', get_permalink(jigoshop_get_page_id('myaccount'))));
			exit;
		}
	}

	if ($isChangePassword){
		$user_id = get_current_user_id();
		if ($_POST && $user_id > 0 && jigoshop::verify_nonce('change_password')) {
			if ($_POST['password-1'] && $_POST['password-2']) {
				if ($_POST['password-1'] == $_POST['password-2']) {
					wp_update_user(array('ID' => $user_id, 'user_pass' => $_POST['password-1']));
					jigoshop::add_message(__('Password changed successfully.', 'jigoshop'));
					wp_safe_redirect(apply_filters('jigoshop_get_myaccount_page_id', get_permalink(jigoshop_get_page_id(JIGOSHOP_MY_ACCOUNT))));
					exit;
				} else {
					jigoshop::add_error(__('Passwords do not match.', 'jigoshop'));
				}
			} else {
				jigoshop::add_error(__('Please enter your password.', 'jigoshop'));
			}
		}
	}
});
