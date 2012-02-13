<?php
/**
 * $options_settings variable contains all the options used on the Jigoshop settings page
 *
 * DISCLAIMER
 *
 * Do not edit or add directly to this file if you wish to upgrade Jigoshop to newer
 * versions in the future. If you wish to customise Jigoshop core for your needs,
 * please use our GitHub repository to publish essential changes for consideration.
 *
 * @package		Jigoshop
 * @category	Admin
 * @author		Jigowatt
 * @copyright	Copyright (c) 2011-2012 Jigowatt Ltd.
 * @license		http://jigoshop.com/license/commercial-edition
 */

/**
 * options_settings
 *
 * This variable contains all the options used on the jigoshop settings page
 *
 * @since 		1.0
 * @category 	Admin
 * @usedby 		jigoshop_settings(), jigoshop_default_options()
 */
global $options_settings;

$options_settings = apply_filters('jigoshop_options_settings', array(

	array( 'type' => 'tab', 'tabname' => __('General', 'jigoshop') ),

	array( 'name' => __('General Options', 'jigoshop'), 'type' => 'title', 'desc' 		=> '' ),

	array(
		'name' => __('Demo store','jigoshop'),
		'desc' 		=> '',
		'tip' 		=> __('Enable this option to show a banner at the top of every page stating this shop is currently in testing mode.','jigoshop'),
		'id' 		=> 'jigoshop_demo_store',
		'css' 		=> 'min-width:100px;',
		'std' 		=> 'no',
		'type' 		=> 'select',
		'options' => array(
			'yes' => __('Yes', 'jigoshop'),
			'no'  => __('No', 'jigoshop')
		)
	),

	array(
		'name' => __('Send Jigoshop emails from','jigoshop'),
		'desc' 		=> '',
		'tip' 		=> __('The email used to send all Jigoshop related emails, such as order confirmations and notices.','jigoshop'),
		'id' 		=> 'jigoshop_email',
		'css' 		=> 'width:250px;',
		'type' 		=> 'text',
		'std' 		=> get_option('admin_email')
	),

	array(
		'name' => __('Base Country/Region','jigoshop'),
		'desc' 		=> '',
		'tip' 		=> __('This is the base country for your business. Tax rates will be based on this country.','jigoshop'),
		'id' 		=> 'jigoshop_default_country',
		'css' 		=> '',
		'std' 		=> 'GB',
		'type' 		=> 'single_select_country'
	),

	array(
		'name' => __('Allowed Countries','jigoshop'),
		'desc' 		=> '',
		'tip' 		=> __('These are countries that you are willing to ship to.','jigoshop'),
		'id' 		=> 'jigoshop_allowed_countries',
		'css' 		=> 'min-width:100px;',
		'std' 		=> 'all',
		'type' 		=> 'select',
		'options' => array(
			'all'  => __('All Countries', 'jigoshop'),
			'specific' => __('Specific Countries', 'jigoshop')
		)
	),

	array(
		'name' => __('Specific Countries','jigoshop'),
		'desc' 		=> '',
		'tip' 		=> '',
		'id' 		=> 'jigoshop_specific_allowed_countries',
		'css' 		=> '',
		'std' 		=> '',
		'type' 		=> 'multi_select_countries'
	),

	array(
		'name' => __('After adding product to cart','jigoshop'),
		'desc' 		=> '',
		'tip' 		=> __('Define what should happen when a user clicks on &#34;Add to Cart&#34; on any product or page.','jigoshop'),
		'id' 		=> 'jigoshop_redirect_add_to_cart',
		'css' 		=> 'min-width:100px;',
		'std' 		=> 'same_page',
		'type' 		=> 'select',
		'options' => array(
			'same_page'  => __('Stay on the same page', 'jigoshop'),
			'to_checkout' => __('Redirect to Checkout', 'jigoshop'),
			'to_cart' => __('Redirect to Cart', 'jigoshop'),
		)
	),

	array(
		'name' => __('Disable Jigoshop frontend.css','jigoshop'),
		'desc' 		=> '',
		'tip' 		=> __('Useful if you want to disable Jigoshop styles and theme it yourself via your theme.','jigoshop'),
		'id' 		=> 'jigoshop_disable_css',
		'css' 		=> 'min-width:100px;',
		'std' 		=> 'no',
		'type' 		=> 'select',
		'options' => array(
			'no'  => __('No', 'jigoshop'),
			'yes' => __('Yes', 'jigoshop')
		)
	),

	array(
		'name' => __('Disable bundled Fancybox','jigoshop'),
		'desc' 		=> '',
		'tip' 		=> __('Useful if or one of your plugin already loads the Fancybox script and css. But be careful, Jigoshop will still try to open product images using Fancybox.','jigoshop'),
		'id' 		=> 'jigoshop_disable_fancybox',
		'css' 		=> 'min-width:100px;',
		'std' 		=> 'no',
		'type' 		=> 'select',
		'options' => array(
			'no'  => __('No', 'jigoshop'),
			'yes' => __('Yes', 'jigoshop')
		)
	),

	array( 'name' => __('Checkout options', 'jigoshop'), 'type' => 'title', 'desc' 		=> '' ),

	array(
		'name' => __('Allow guest purchases','jigoshop'),
		'desc' 		=> '',
		'tip' 		=> __('Setting this to Yes will allow users to checkout without registering or signing up. Otherwise, users must be signed in or must sign up to checkout.','jigoshop'),
		'id' 		=> 'jigoshop_enable_guest_checkout',
		'css' 		=> 'min-width:100px;',
		'std' 		=> 'yes',
		'type' 		=> 'select',
		'options' => array(
			'yes' => __('Yes', 'jigoshop'),
			'no'  => __('No', 'jigoshop')
		)
	),

	array(
		'name' => __('Show login form','jigoshop'),
		'desc' 		=> '',
		'id' 		=> 'jigoshop_enable_guest_login',
		'css' 		=> 'min-width:100px;',
		'std' 		=> 'yes',
		'type' 		=> 'select',
		'options' => array(
			'yes' => __('Yes', 'jigoshop'),
			'no'  => __('No', 'jigoshop')
		)
	),

	array(
		'name' => __('Allow registration','jigoshop'),
		'desc' 		=> '',
		'id' 		=> 'jigoshop_enable_signup_form',
		'css' 		=> 'min-width:100px;',
		'std' 		=> 'yes',
		'type' 		=> 'select',
		'options' => array(
			'yes' => __('Yes', 'jigoshop'),
			'no'  => __('No', 'jigoshop')
		)
	),

	array(
		'name' => __('Force SSL on checkout','jigoshop'),
		'desc' 		=> '',
		'tip' 		=> __('Forcing SSL is recommended. This will load your checkout page with https://. An SSL certificate is <strong>required</strong> if you choose yes. Contact your hosting provider for more information on SSL Certs.','jigoshop'),
		'id' 		=> 'jigoshop_force_ssl_checkout',
		'css' 		=> 'min-width:100px;',
		'std' 		=> 'no',
		'type' 		=> 'select',
		'options' => array(
			'yes' => __('Yes', 'jigoshop'),
			'no'  => __('No', 'jigoshop')
		)
	),

	array( 'name' => __('Integration', 'jigoshop'), 'type' => 'title', 'desc' 		=> '' ),

	array(
		'name' => __('ShareThis Publisher ID','jigoshop'),
		'desc' 		=> __("Enter your <a href='http://sharethis.com/account/'>ShareThis publisher ID</a> to show ShareThis on product pages.",'jigoshop'),
		'tip' 		=> __('ShareThis is a small social sharing widget for posting links on popular sites such as Twitter and Facebook.','jigoshop'),
		'id' 		=> 'jigoshop_sharethis',
		'css' 		=> 'width:300px;',
		'type' 		=> 'text',
		'std' 		=> ''
	),

	array(
		'name' => __('Google Analytics ID', 'jigoshop'),
		'desc' 		=> __('Log into your Google Analytics account to find your ID. e.g. <code>UA-XXXXXXX-X</code>', 'jigoshop'),
		'id' 		=> 'jigoshop_ga_id',
		'type' 		=> 'text',
        'css' 		=> 'min-width:300px;',
	),

	array(
		'name' => __('Enable eCommerce Tracking', 'jigoshop'),
		'tip' 		=> __('Add Google Analytics eCommerce tracking code upon successful orders', 'jigoshop'),
		'desc'		=> __('<a href="//support.google.com/analytics/bin/answer.py?hl=en&answer=1009612">Learn how to enable</a> eCommerce tracking for your Google Analytics account.', 'jigoshop'),
		'id' 		=> 'jigoshop_ga_ecommerce_tracking_enabled',
		'type' 		=> 'select',
		'options' => array(
			'no'  => __('No', 'jigoshop'),
			'yes' => __('Yes', 'jigoshop')
		)
	),

	array( 'type' => 'tabend'),

	array( 'type' => 'tab', 'tabname' => __('Pages', 'jigoshop') ),

	array( 'name' => __('Shop page configuration', 'jigoshop'), 'type' => 'title', 'desc' 		=> '' ),

	array(
		'name' => __('Cart Page','jigoshop'),
		'desc' 		=> __('Shortcode to place on page: <code>[jigoshop_cart]</code>','jigoshop'),
		'tip' 		=> '',
		'id' 		=> 'jigoshop_cart_page_id',
		'css' 		=> 'min-width:50px;',
		'type' 		=> 'single_select_page',
		'std' 		=> ''
	),

	array(
		'name' => __('Checkout Page','jigoshop'),
		'desc' 		=> __('Shortcode to place on page: <code>[jigoshop_checkout]</code>','jigoshop'),
		'tip' 		=> '',
		'id' 		=> 'jigoshop_checkout_page_id',
		'css' 		=> 'min-width:50px;',
		'type' 		=> 'single_select_page',
		'std' 		=> ''
	),

	array(
		'name' => __('Pay Page','jigoshop'),
		'desc' 		=> __('Shortcode to place on page: <code>[jigoshop_pay]</code><br/>Default parent page: Checkout','jigoshop'),
		'tip' 		=> '',
		'id' 		=> 'jigoshop_pay_page_id',
		'css' 		=> 'min-width:50px;',
		'type' 		=> 'single_select_page',
		'std' 		=> ''
	),

	array(
		'name' => __('Thanks Page','jigoshop'),
		'desc' 		=> __('Shortcode to place on page: <code>[jigoshop_thankyou]</code><br/>Default parent page: Checkout','jigoshop'),
		'tip' 		=> '',
		'id' 		=> 'jigoshop_thanks_page_id',
		'css' 		=> 'min-width:50px;',
		'type' 		=> 'single_select_page',
		'std' 		=> ''
	),

	array(
		'name' => __('My Account Page','jigoshop'),
		'desc' 		=> __('Shortcode to place on page: <code>[jigoshop_my_account]</code>','jigoshop'),
		'tip' 		=> '',
		'id' 		=> 'jigoshop_myaccount_page_id',
		'css' 		=> 'min-width:50px;',
		'type' 		=> 'single_select_page',
		'std' 		=> ''
	),

	array(
		'name' => __('Edit Address Page','jigoshop'),
		'desc' 		=> __('Shortcode to place on page: <code>[jigoshop_edit_address]</code><br/>Default parent page: My Account','jigoshop'),
		'tip' 		=> '',
		'id' 		=> 'jigoshop_edit_address_page_id',
		'css' 		=> 'min-width:50px;',
		'type' 		=> 'single_select_page',
		'std' 		=> ''
	),

	array(
		'name' => __('View Order Page','jigoshop'),
		'desc' 		=> __('Shortcode to place on page:<code>[jigoshop_view_order]</code><br/>Default parent page: My Account','jigoshop'),
		'tip' 		=> '',
		'id' 		=> 'jigoshop_view_order_page_id',
		'css' 		=> 'min-width:50px;',
		'type' 		=> 'single_select_page',
		'std' 		=> ''
	),

	array(
		'name' => __('Change Password Page','jigoshop'),
		'desc' 		=> __('Shortcode to place on page: <code>[jigoshop_change_password]</code><br/>Default parent page: My Account','jigoshop'),
		'tip' 		=> '',
		'id' 		=> 'jigoshop_change_password_page_id',
		'css' 		=> 'min-width:50px;',
		'type' 		=> 'single_select_page',
		'std' 		=> ''
	),

	array(
		'name' => __('Track Order Page','jigoshop'),
		'desc' 		=> __('Shortcode to place on page: <code>[jigoshop_order_tracking]</code>','jigoshop'),
		'tip' 		=> '',
		'id' 		=> 'jigoshop_track_order_page_id',
		'css' 		=> 'min-width:50px;',
		'type' 		=> 'single_select_page',
		'std' 		=> ''
	),

	array(
		'name' => __('Terms Page', 'jigoshop'),
		'desc' 		=> __('If you define a &#34;Terms&#34; page the customer will be asked to accept it before allowing them to place their order.', 'jigoshop'),
		'tip' 		=> '',
		'id' 		=> 'jigoshop_terms_page_id',
		'css' 		=> 'min-width:50px;',
		'std' 		=> '',
		'type' 		=> 'single_select_page',
		'args'		=> 'show_option_none=' . __('None', 'jigoshop'),
	),

	array( 'type' => 'tabend'),

	array( 'type' 		=> 'tab', 'tabname' => __('Catalog &amp; Pricing', 'jigoshop') ),

	array(	'name' => __('Catalog Options', 'jigoshop'), 'type' 		=> 'title','desc' 		=> '', 'id' 		=> '' ),


	array(
		'name' => __('Catalog base page','jigoshop'),
		'desc'		=> '',
		'tip' 		=> __('This sets the base page of your shop. You should not change this value once you have launched your site otherwise you risk breaking urls of other sites pointing to yours, etc.','jigoshop'),
		'id' 		=> 'jigoshop_shop_page_id',
		'css' 		=> 'min-width:50px;',
		'type' 		=> 'single_select_page',
		'std' 		=> ''
	),

	array(
		'name' => __('Prepend links with base page','jigoshop'),
		'desc'		=> '',
		'tip' 		=> __('This will only apply to tags &amp; categories.<br/>Yes: http://yoursite.com / product_category / YourCategory<br/>No: http://yoursite.com / base_page / product_category / YourCategory', 'jigoshop'),
		'id' 		=> 'jigoshop_prepend_shop_page_to_urls',
		'css' 		=> 'min-width:100px;',
		'std' 		=> 'no',
		'type' 		=> 'select',
		'options' => array(
			'no'  => __('No', 'jigoshop'),
			'yes' => __('Yes', 'jigoshop')
		)
	),

	array(
		'name' => __('Sort products in catalog by','jigoshop'),
		'desc' 		=> '',
		'tip' 		=> __('Determines the display sort order of products for the Shop, Categories, and Tag pages.','jigoshop'),
		'id' 		=> 'jigoshop_catalog_sort_orderby',
		'css' 		=> 'min-width:100px;',
		'std' 		=> 'post_date',
		'type' 		=> 'select',
		'options' => array(
			'post_date' => __('Creation Date', 'jigoshop'),
			'title'  => __('Product Title', 'jigoshop'),
			'menu_order'  => __('Product Post Order', 'jigoshop')
		)
	),

	array(
		'name' => __('Catalog sort direction','jigoshop'),
		'desc' 		=> '',
		'tip' 		=> __('Determines whether the catalog sort orderby is ascending or descending.','jigoshop'),
		'id' 		=> 'jigoshop_catalog_sort_direction',
		'css' 		=> 'min-width:100px;',
		'std' 		=> 'asc',
		'type' 		=> 'select',
		'options' => array(
			'asc' => __('Ascending', 'jigoshop'),
			'desc'  => __('Descending', 'jigoshop')
		)
	),

	array(
		'name' => __('Catalog products per row','jigoshop'),
		'desc' 		=> __('Default = 3','jigoshop'),
		'tip' 		=> __('Determines how many products to show on one display row for Shop, Category and Tag pages.','jigoshop'),
		'id' 		=> 'jigoshop_catalog_columns',
		'css' 		=> 'width:30px;',
		'std' 		=> '3',
		'type' 		=> 'text',
	),

	array(
		'name' => __('Catalog products per page','jigoshop'),
		'desc' 		=> __('Default = 12','jigoshop'),
		'tip' 		=> __('Determines how many products to display on Shop, Category and Tag pages before needing next and previous page navigation.','jigoshop'),
		'id' 		=> 'jigoshop_catalog_per_page',
		'css' 		=> 'width:30px;',
		'std' 		=> '12',
		'type' 		=> 'text',
	),

	array(	'name' => __('Pricing Options', 'jigoshop'), 'type' 		=> 'title','desc' 		=> '', 'id' 		=> '' ),

	array(
		'name' => __('Currency', 'jigoshop'),
		'desc' 		=> sprintf( __("This controls what currency prices are listed at in the catalog, and which currency PayPal, and other gateways, will take payments in. See the list of supported <a target='_new' href='%s'>PayPal currencies</a>.", 'jigoshop'), 'https://www.paypal.com/cgi-bin/webscr?cmd=p/sell/mc/mc_intro-outside' ),
		'tip' 		=> '',
		'id' 		=> 'jigoshop_currency',
		'css' 		=> 'min-width:200px;',
		'std' 		=> 'GBP',
		'type' 		=> 'select',
		'options' => apply_filters('jigoshop_currencies', array(
			'AED' => __('United Arab Emirates dirham (&#1583;&#46;&#1573;)', 'jigoshop'),
			'AUD' => __('Australian Dollar (&#36;)', 'jigoshop'),
			'BRL' => __('Brazilian Real (&#82;&#36;)', 'jigoshop'),
			'CAD' => __('Canadian Dollar (&#36;)', 'jigoshop'),
			'CHF' => __('Swiss Franc (SFr.)', 'jigoshop'),
			'CNY' => __('Chinese yuan (&#165;)', 'jigoshop'),
			'CZK' => __('Czech Koruna (&#75;&#269;)', 'jigoshop'),
			'DKK' => __('Danish Krone (kr)', 'jigoshop'),
			'EUR' => __('Euro (&euro;)', 'jigoshop'),
			'GBP' => __('Pounds Sterling (&pound;)', 'jigoshop'),
			'HKD' => __('Hong Kong Dollar (&#36;)', 'jigoshop'),
			'HRK' => __('Croatian Kuna (&#107;&#110;)', 'jigoshop'),
			'HUF' => __('Hungarian Forint (&#70;&#116;)', 'jigoshop'),
			'IDR' => __('Indonesia Rupiah (&#82;&#112;)', 'jigoshop'),
			'ILS' => __('Israeli Shekel (&#8362;)', 'jigoshop'),
			'INR' => __('Indian Rupee (&#8360;)', 'jigoshop'),
			'JPY' => __('Japanese Yen (&yen;)', 'jigoshop'),
			'MXN' => __('Mexican Peso (&#36;)', 'jigoshop'),
			'MYR' => __('Malaysian Ringgits (RM)', 'jigoshop'),
			'NGN' => __('Nigerian Naira (&#8358;)', 'jigoshop'),
			'NOK' => __('Norwegian Krone (kr)', 'jigoshop'),
			'NZD' => __('New Zealand Dollar (&#36;)', 'jigoshop'),
			'PHP' => __('Philippine Pesos (&#8369;)', 'jigoshop'),
			'PLN' => __('Polish Zloty (&#122;&#322;)', 'jigoshop'),
			'RON' => __('Romanian New Leu (&#108;&#101;&#105;)', 'jigoshop'),
			'RUB' => __('Russian Ruble (&#1088;&#1091;&#1073;)', 'jigoshop'),
			'SEK' => __('Swedish Krona (kr)', 'jigoshop'),
			'SGD' => __('Singapore Dollar (&#36;)', 'jigoshop'),
			'THB' => __('Thai Baht (&#3647;)', 'jigoshop'),
			'TRY' => __('Turkish Lira (&#8356;)', 'jigoshop'),
			'TWD' => __('Taiwan New Dollar (&#36;)', 'jigoshop'),
			'USD' => __('US Dollar (&#36;)', 'jigoshop'),
			'ZAR' => __('South African rand (R)', 'jigoshop')
			)
		)
	),

	array(
		'name' => __('Currency display', 'jigoshop'),
		'desc' 		=> __("This controls the display of the currency symbol and currency code.", 'jigoshop'),
		'tip' 		=> '',
		'id' 		=> 'jigoshop_currency_pos',
		'css' 		=> 'min-width:200px;',
		'std' 		=> 'left',
		'type' 		=> 'select',
		'options' => array(
			'left' => __(get_jigoshop_currency_symbol() . '0' . get_option('jigoshop_price_decimal_sep') . '00', 'jigoshop'),
			'left_space' => __(get_jigoshop_currency_symbol() . ' 0' . get_option('jigoshop_price_decimal_sep') . '00', 'jigoshop'),
			'right' => __('0' . get_option('jigoshop_price_decimal_sep') . '00' . get_jigoshop_currency_symbol(), 'jigoshop'),
			'right_space' => __('0' . get_option('jigoshop_price_decimal_sep') . '00 ' . get_jigoshop_currency_symbol(), 'jigoshop'),
			'left_code' => __(get_option('jigoshop_currency') . '0' . get_option('jigoshop_price_decimal_sep') . '00', 'jigoshop'),
			'left_code_space' => __(get_option('jigoshop_currency') . ' 0' . get_option('jigoshop_price_decimal_sep') . '00', 'jigoshop'),
			'right_code' => __('0' . get_option('jigoshop_price_decimal_sep') . '00' . get_option('jigoshop_currency'), 'jigoshop'),
			'right_code_space' => __('0' . get_option('jigoshop_price_decimal_sep') . '00 ' . get_option('jigoshop_currency'), 'jigoshop'),
			'symbol_code' => __(get_jigoshop_currency_symbol() . '0' . get_option('jigoshop_price_decimal_sep') . '00' . get_option('jigoshop_currency'), 'jigoshop'),
			'symbol_code_space' => __(get_jigoshop_currency_symbol() . ' 0' . get_option('jigoshop_price_decimal_sep') . '00 ' . get_option('jigoshop_currency'), 'jigoshop'),
			'code_symbol' => __(get_option('jigoshop_currency') . '0' . get_option('jigoshop_price_decimal_sep') . '00' . get_jigoshop_currency_symbol(), 'jigoshop'),
			'code_symbol_space' => __(get_option('jigoshop_currency') . ' 0' . get_option('jigoshop_price_decimal_sep') . '00 ' . get_jigoshop_currency_symbol(), 'jigoshop'),
		)
	),

	array(
		'name' => __('Thousand separator', 'jigoshop'),
		'desc' 		=> __('This sets the thousand separator of displayed prices.', 'jigoshop'),
		'tip' 		=> '',
		'id' 		=> 'jigoshop_price_thousand_sep',
		'css' 		=> 'width:30px;',
		'std' 		=> ',',
		'type' 		=> 'text',
	),

	array(
		'name' => __('Decimal separator', 'jigoshop'),
		'desc' 		=> __('This sets the decimal separator of displayed prices.', 'jigoshop'),
		'tip' 		=> '',
		'id' 		=> 'jigoshop_price_decimal_sep',
		'css' 		=> 'width:30px;',
		'std' 		=> '.',
		'type' 		=> 'text',
	),

	array(
		'name' => __('Number of decimals', 'jigoshop'),
		'desc' 		=> __('This sets the number of decimal points shown in displayed prices.', 'jigoshop'),
		'tip' 		=> '',
		'id' 		=> 'jigoshop_price_num_decimals',
		'css' 		=> 'width:30px;',
		'std' 		=> '2',
		'type' 		=> 'text',
	),

	array( 'type' => 'tabend'),

	array( 'type' => 'tab', 'tabname' => __('Images', 'jigoshop') ),

	array( 'name' => __('Image Options', 'jigoshop'), 'type' => 'title','desc' => __('Large variations from the defaults could require CSS modifications in your Theme.','jigoshop'), 'id' => '' ),

	array(
		'name' 		=> __('Tiny Images','jigoshop'),
		'tip'		=> '',
		'desc' 		=> __('The small image used in the Cart, Checkout, Orders and Widgets','jigoshop'),
		'id' 		=> 'jigoshop_shop_tiny',
		'css' 		=> '',
		'type' 		=> 'image_size',
		'std' 		=> 36
	),

	array(
		'name' 		=> __('Thumbnail Images','jigoshop'),
		'desc' 		=> __('The thumbnail image for Single Product page extra images.','jigoshop'),
		'id' 		=> 'jigoshop_shop_thumbnail',
		'css' 		=> '',
		'type' 		=> 'image_size',
		'std' 		=> 90
	),

	array(
		'name' => __( 'Catalog Images', 'jigoshop' ),
		'desc' 		=> __('The catalog image for Shop, Categories, Tags, and Related Products.', 'jigoshop'),
		'id' 		=> 'jigoshop_shop_small',
		'css' 		=> '',
		'type' 		=> 'image_size',
		'std' 		=> 150
	),

	array(
		'name' 		=> __('Large Images','jigoshop'),
		'desc' 		=> __('Single Product page\'s large or Featured image.','jigoshop'),
		'id' 		=> 'jigoshop_shop_large',
		'css' 		=> '',
		'type' 		=> 'image_size',
		'std' 		=> 300
	),

	array( 'type' => 'tabend'),

	array( 'type' => 'tab', 'tabname' => __('Coupons', 'jigoshop') ),

	array( 'name' => __('Coupon Information', 'jigoshop'), 'type' => 'title', 'desc' 		=> __('<br /><div>Coupons allow you to give your customers special offers and discounts. Leave product ID&#39;s blank to apply to all products in the cart. Separate each product ID with a comma.</div><br /><div>Use either flat rates or percentage discounts for both cart totals and individual products. (do not enter a % sign, just a number). Product percentage discounts <strong>must</strong> have a product ID to be applied, otherwise use Cart Percentage Discount for all products.</div><br /><div>"<em>Alone</em>" means <strong>only</strong> that coupon will be allowed for the whole cart.  If you have several of these, the last one entered by the customer will be used.</div>','jigoshop') ),

	array(
		'name' => __('Coupons','jigoshop'),
		'desc' 		=> __('All fields are required.','jigoshop'),
		'id' 		=> 'jigoshop_coupons',
		'css' 		=> 'min-width:50px;',
		'type' 		=> 'coupons',
		'std' 		=> ''
	),

	array( 'type' => 'tabend'),

	array( 'type' 		=> 'tab', 'tabname' => __('Products &amp; Inventory', 'jigoshop') ),

	array( 'name' => __('Product Options', 'jigoshop'), 'type' => 'title', 'desc' 	=> '' ),

	array(
		'name' => __('Enable SKU field','jigoshop'),
		'desc' 		=> '',
		'tip' 		=> __('Turning off the SKU field will give products an SKU of their post id.','jigoshop'),
		'id' 		=> 'jigoshop_enable_sku',
		'css' 		=> 'min-width:100px;',
		'std' 		=> 'no',
		'type' 		=> 'select',
		'options' => array(
			'yes' => __('Yes', 'jigoshop'),
			'no'  => __('No', 'jigoshop')
		)
	),

	array(
		'name' => __('Enable weight field','jigoshop'),
		'desc' 		=> '',
		'tip' 		=> '',
		'id' 		=> 'jigoshop_enable_weight',
		'css' 		=> 'min-width:100px;',
		'std' 		=> 'yes',
		'type' 		=> 'select',
		'options' => array(
			'yes' => __('Yes', 'jigoshop'),
			'no'  => __('No', 'jigoshop')
		)
	),

	array(
		'name' => __('Weight Unit', 'jigoshop'),
		'desc' 		=> __("This controls what unit you will define weights in.", 'jigoshop'),
		'tip' 		=> '',
		'id' 		=> 'jigoshop_weight_unit',
		'css' 		=> 'min-width:200px;',
		'std' 		=> 'kg',
		'type' 		=> 'select',
		'options' => array(
			'kg' => __('Kilograms', 'jigoshop'),
			'lbs' => __('Pounds', 'jigoshop')
		)
	),

	array(
		'name' => __('Enable product dimensions','jigoshop'),
		'desc' 		=> '',
		'tip' 		=> '',
		'id' 		=> 'jigoshop_enable_dimensions',
		'css' 		=> 'min-width:100px;',
		'std' 		=> 'yes',
		'type' 		=> 'select',
		'options' => array(
			'yes' => __('Yes', 'jigoshop'),
			'no'  => __('No', 'jigoshop')
		)
	),

	array(
		'name' => __('Dimensions Unit', 'jigoshop'),
		'desc' 		=> __("This controls what unit you will define dimensions in.", 'jigoshop'),
		'tip' 		=> '',
		'id' 		=> 'jigoshop_dimension_unit',
		'css' 		=> 'min-width:200px;',
		'std' 		=> 'cm',
		'type' 		=> 'select',
		'options' => array(
			'cm' => __('centimeters', 'jigoshop'),
			'in' => __('inches', 'jigoshop')
		)
	),

	array(	'name' => __('Inventory Options', 'jigoshop'), 'type' 		=> 'title','desc' 		=> '', 'id' 		=> '' ),

	array(
		'name' => __('Manage stock','jigoshop'),
		'desc' 		=> __('If you are not managing stock, turn it off here to disable it in admin and on the front-end.','jigoshop'),
		'tip' 		=> __('You can manage stock on a per-item basis if you leave this option on.', 'jigoshop'),
		'id' 		=> 'jigoshop_manage_stock',
		'css' 		=> 'min-width:100px;',
		'std' 		=> 'yes',
		'type' 		=> 'select',
		'options' => array(
			'yes' => __('Yes', 'jigoshop'),
			'no'  => __('No', 'jigoshop')
		)
	),

	array(
		'name' => __('Show stock amounts','jigoshop'),
		'desc' 		=> '',
		'tip' 		=> __('Set to yes to allow customers to view the amount of stock available for a product.', 'jigoshop'),
		'id' 		=> 'jigoshop_show_stock',
		'css' 		=> 'min-width:100px;',
		'std' 		=> 'yes',
		'type' 		=> 'select',
		'options' => array(
			'yes' => __('Yes', 'jigoshop'),
			'no'  => __('No', 'jigoshop')
		)
	),

	array(
		'name' => __('Notify on low stock','jigoshop'),
		'desc' 		=> '',
		'id' 		=> 'jigoshop_notify_low_stock',
		'css' 		=> 'min-width:100px;',
		'std' 		=> 'yes',
		'type' 		=> 'select',
		'options' => array(
			'yes' => __('Yes', 'jigoshop'),
			'no'  => __('No', 'jigoshop')
		)
	),

	array(
		'name' => __('Low stock threshold','jigoshop'),
		'desc' 		=> '',
		'tip' 		=> __('You will receive a notification as soon this threshold is hit (if notifications are turned on).', 'jigoshop'),
		'id' 		=> 'jigoshop_notify_low_stock_amount',
		'css' 		=> 'min-width:50px;',
		'type' 		=> 'text',
		'std' 		=> '2'
	),

	array(
		'name' => __('Notify on out of stock','jigoshop'),
		'desc' 		=> '',
		'id' 		=> 'jigoshop_notify_no_stock',
		'css' 		=> 'min-width:100px;',
		'std' 		=> 'yes',
		'type' 		=> 'select',
		'options' => array(
			'yes' => __('Yes', 'jigoshop'),
			'no'  => __('No', 'jigoshop')
		)
	),

	array(
		'name' => __('Out of stock threshold','jigoshop'),
		'desc' 		=> '',
		'tip' 		=> __('You will receive a notification as soon this threshold is hit (if notifications are turned on).', 'jigoshop'),
		'id' 		=> 'jigoshop_notify_no_stock_amount',
		'css' 		=> 'min-width:50px;',
		'type' 		=> 'text',
		'std' 		=> '0'
	),

	array(
		'name' => __('Hide out of stock products','jigoshop'),
		'desc' 		=> '',
		'tip' 		=> 'For Yes: When the Out of Stock Threshold (above) is reached, the product visibility will be set to hidden so that it will not appear on the Catalog or Shop product lists.',
		'id' 		=> 'jigoshop_hide_no_stock_product',
		'css' 		=> 'min-width:100px;',
		'std' 		=> 'no',
		'type' 		=> 'select',
		'options' => array(
			'no'  => __('No', 'jigoshop'),
			'yes' => __('Yes', 'jigoshop')
		)
	),

	array( 'type' => 'tabend'),

	array( 'type' 		=> 'tab', 'tabname' => __('Shipping', 'jigoshop') ),

	array(	'name' => __('Shipping Options', 'jigoshop'), 'type' 		=> 'title','desc' 		=> '', 'id' 		=> '' ),

	array(
		'name' => __('Calculate Shipping','jigoshop'),
		'desc' 		=> __('Only set this to no if you are not shipping items, or items have shipping costs included.','jigoshop'),
		'tip' 		=> __('If you are not calculating shipping then you can ignore all other tax options.', 'jigoshop'),
		'id' 		=> 'jigoshop_calc_shipping',
		'css' 		=> 'min-width:100px;',
		'std' 		=> 'yes',
		'type' 		=> 'select',
		'options' => array(
			'yes' => __('Yes', 'jigoshop'),
			'no'  => __('No', 'jigoshop')
		)
	),

	array(
		'name' => __('Enable shipping calculator on cart','jigoshop'),
		'desc' 		=> '',
		'tip' 		=> '',
		'id' 		=> 'jigoshop_enable_shipping_calc',
		'css' 		=> 'min-width:100px;',
		'std' 		=> 'yes',
		'type' 		=> 'select',
		'options' => array(
			'yes' => __('Yes', 'jigoshop'),
			'no'  => __('No', 'jigoshop')
		)
	),

	array(
		'name' => __('Only ship to billing address?','jigoshop'),
		'desc' 		=> '',
		'tip' 		=> '',
		'id' 		=> 'jigoshop_ship_to_billing_address_only',
		'css' 		=> 'min-width:100px;',
		'std' 		=> 'no',
		'type' 		=> 'select',
		'options' => array(
			'yes' => __('Yes', 'jigoshop'),
			'no'  => __('No', 'jigoshop')
		)
	),

	array( 'type' => 'shipping_options'),

	array( 'type' => 'tabend'),

	array( 'type' 		=> 'tab', 'tabname' => __('Tax', 'jigoshop') ),

	array(	'name' => __('Tax Options', 'jigoshop'), 'type' 		=> 'title','desc' 		=> '', 'id' 		=> '' ),

	array(
		'name' => __('Calculate Taxes','jigoshop'),
		'desc' 		=> __('Only set this to no if you are exclusively selling non-taxable items.','jigoshop'),
		'tip' 		=> __('If you are not calculating taxes then you can ignore all other tax options.', 'jigoshop'),
		'id' 		=> 'jigoshop_calc_taxes',
		'css' 		=> 'min-width:100px;',
		'std' 		=> 'yes',
		'type' 		=> 'select',
		'options' => array(
			'yes' => __('Yes', 'jigoshop'),
			'no'  => __('No', 'jigoshop')
		)
	),

	array(
		'name' => __('Catalog Prices include tax?','jigoshop'),
		'desc' 		=> '',
		'tip' 		=> __('If prices include tax then tax calculations will work backwards.','jigoshop'),
		'id' 		=> 'jigoshop_prices_include_tax',
		'css' 		=> 'min-width:100px;',
		'std' 		=> 'yes',
		'type' 		=> 'select',
		'options' => array(
			'yes' => __('Yes', 'jigoshop'),
			'no'  => __('No', 'jigoshop')
		)
	),

	array(
		'name' => __('Cart totals display...','jigoshop'),
		'desc' 		=> '',
		'tip' 		=> __('Should the subtotal be shown including or excluding tax on the frontend?','jigoshop'),
		'id' 		=> 'jigoshop_display_totals_tax',
		'css' 		=> 'min-width:100px;',
		'std' 		=> 'excluding',
		'type' 		=> 'select',
		'options' => array(
			'including' => __('price including tax', 'jigoshop'),
			'excluding'  => __('price excluding tax', 'jigoshop')
		)
	),

	array(
		'name' => __('Additional Tax classes','jigoshop'),
		'desc' 		=> __('List 1 per line. This is in addition to the default <em>Standard Rate</em>.','jigoshop'),
		'tip' 		=> __('List product and shipping tax classes here, e.g. Zero Tax, Reduced Rate.','jigoshop'),
		'id' 		=> 'jigoshop_tax_classes',
		'css' 		=> 'width:100%; height: 75px;',
		'type' 		=> 'textarea',
		'std' 		=> "Reduced Rate\nZero Rate"
	),

	array(
		'name' => __('Tax rates','jigoshop'),
		'desc' 		=> __('All fields are required.','jigoshop'),
		'tip' 		=> __('To avoid rounding errors, insert tax rates with 4 decimal places.','jigoshop'),
		'id' 		=> 'jigoshop_tax_rates',
		'css' 		=> 'min-width:50px;',
		'type' 		=> 'tax_rates',
		'std' 		=> ''
	),

	array( 'type' => 'tabend'),

	array( 'type' 		=> 'tab', 'tabname' => __('Payment Gateways', 'jigoshop') ),

	array( 'type' => 'gateway_options'),

	array( 'type' => 'tabend')

) );
