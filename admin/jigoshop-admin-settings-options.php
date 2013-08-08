<?php
/**
 * $jigoshop_options_settings variable contains all the options used on the Jigoshop settings page
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
 * @copyright           Copyright © 2011-2013 Jigoshop.
 * @license             http://jigoshop.com/license/commercial-edition
 */





/***********************    NOTE:   AS OF JIGOSHOP 1.3, THIS FILE IS NO LONGER IN USE       ************************/
_deprecated_file( __FILE__, '1.3', 'Jigoshop_Base::get_options()');





/**
 * options_settings
 *
 * This variable contains all the options used on the jigoshop settings page
 *
 * @since 		1.0
 * @category 	Admin
 * @usedby 		jigoshop_settings(), jigoshop_default_options()
 */
global $jigoshop_options_settings;

$jigoshop_options_settings = apply_filters('jigoshop_options_settings', array(

	array( 'type'        => 'tab', 'tabname'                                 => __('General', 'jigoshop') ),

	array( 'name'        => __('General Options', 'jigoshop'), 'type'        => 'title', 'desc' => '' ),

	array(
		'name'           => __('Send Jigoshop emails from','jigoshop'),
		'desc'           => '',
		'tip'            => __('The email used to send all Jigoshop related emails, such as order confirmations and notices.','jigoshop'),
		'id'             => 'jigoshop_email',
		'css'            => 'width:250px;',
		'type'           => 'text',
		'std'            => get_option('admin_email')
	),

	array(
		'name'           => __('Base Country/Region','jigoshop'),
		'desc'           => '',
		'tip'            => __('This is the base country for your business. Tax rates will be based on this country.','jigoshop'),
		'id'             => 'jigoshop_default_country',
		'css'            => '',
		'std'            => 'GB',
		'type'           => 'single_select_country'
	),

	array(
		'name'           => __('Allowed Countries','jigoshop'),
		'desc'           => '',
		'tip'            => __('These are countries that you are willing to ship to.','jigoshop'),
		'id'             => 'jigoshop_allowed_countries',
		'css'            => 'min-width:100px;',
		'std'            => 'all',
		'type'           => 'select',
		'options'        => array(
			'all'        => __('All Countries', 'jigoshop'),
			'specific'   => __('Specific Countries', 'jigoshop')
		)
	),

	array(
		'name'           => __('Specific Countries','jigoshop'),
		'desc'           => '',
		'tip'            => '',
		'id'             => 'jigoshop_specific_allowed_countries',
		'css'            => '',
		'std'            => '',
		'type'           => 'multi_select_countries'
	),

	array(
		'name'           => __('After adding product to cart','jigoshop'),
		'desc'           => '',
		'tip'            => __('Define what should happen when a user clicks on &#34;Add to Cart&#34; on any product or page.','jigoshop'),
		'id'             => 'jigoshop_redirect_add_to_cart',
		'css'            => 'min-width:100px;',
		'std'            => 'same_page',
		'type'           => 'select',
		'options'        => array(
			'same_page'  => __('Stay on the same page', 'jigoshop'),
			'to_checkout'=> __('Redirect to Checkout', 'jigoshop'),
			'to_cart'    => __('Redirect to Cart', 'jigoshop'),
		)
	),

	array(
		'name'           => __('Downloads','jigoshop'),
		'desc'           => __('Enforce login for downloads','jigoshop'),
		'tip'            => __('If a guest purchases a download, the guest can still download a link without logging in. We recommend disabling guest purchases if you enable this option.','jigoshop'),
		'id'             => 'jigoshop_downloads_require_login',
		'std'            => 'no',
		'type'           => 'checkbox',
	),

	/* Styles and scripts */

	array(
		'name'           => __('Styles and scripts','jigoshop'),
		'desc'           => __('Demo store banner','jigoshop'),
		'tip'            => __('Enable this option to show a banner at the top of every page stating this shop is currently in testing mode.','jigoshop'),
		'id'             => 'jigoshop_demo_store',
		'std'            => 'no',
		'type'           => 'checkbox',
	),

	array(
		'desc'           => __('Disable Jigoshop frontend.css','jigoshop'),
		'tip'            => __('Useful if you want to disable Jigoshop styles and theme it yourself via your theme.','jigoshop'),
		'id'             => 'jigoshop_disable_css',
		'std'            => 'no',
		'type'           => 'checkbox'
	),

	array(
		'desc'           => __('Disable bundled Fancybox','jigoshop'),
		'tip'            => __('Useful if or one of your plugin already loads the Fancybox script and css. But be careful, Jigoshop will still try to open product images using Fancybox.','jigoshop'),
		'id'             => 'jigoshop_disable_fancybox',
		'std'            => 'no',
		'type'           => 'checkbox'
	),

	/* Checkout page */

	array(
		'name'           => __('Checkout page','jigoshop'),
		'desc'           => __('Allow guest purchases','jigoshop'),
		'tip'            => __('Setting this to Yes will allow users to checkout without registering or signing up. Otherwise, users must be signed in or must sign up to checkout.','jigoshop'),
		'id'             => 'jigoshop_enable_guest_checkout',
		'std'            => 'yes',
		'type'           => 'checkbox',
	),

	array(
		'desc'           => __('Show login form','jigoshop'),
		'id'             => 'jigoshop_enable_guest_login',
		'std'            => 'yes',
		'type'           => 'checkbox',
	),

	array(
		'desc'           => __('Allow registration','jigoshop'),
		'id'             => 'jigoshop_enable_signup_form',
		'std'            => 'yes',
		'type'           => 'checkbox',
	),

	array(
		'desc'           => __('Force SSL on checkout','jigoshop'),
		'tip'            => __('Forcing SSL is recommended. This will load your checkout page with https://. An SSL certificate is <strong>required</strong> if you choose yes. Contact your hosting provider for more information on SSL Certs.','jigoshop'),
		'id'             => 'jigoshop_force_ssl_checkout',
		'std'            => 'no',
		'type'           => 'checkbox',
	),

	array(
		'name'           => __('Beta testing', 'jigoshop'),
		'desc'           => __('Use beta versions','jigoshop'),
		'tip'            => __('Only beta plugin updates will be shown for Jigoshop. Beta updates will display normally in the Wordpress plugin manager.','jigoshop'),
		'id'             => 'jigoshop_use_beta_version',
		'std'            => 'no',
		'type'           => 'checkbox',
	),

	array(
		'desc'           => __('Check for update now','jigoshop'),
		'tip'            => __('Manually check if a beta update is available.','jigoshop'),
		'id'             => 'jigoshop_check_beta_now',
		'type'           => 'button',
		'href'           => is_multisite() ? admin_url().'network/' : '' . 'admin.php?page=jigoshop_settings&amp;action=jigoshop_beta_check&amp;_wpnonce='.wp_create_nonce('jigoshop_check_beta_'.get_current_user_id().'_wpnonce')
	),

	array( 'name'        => __('Invoicing', 'jigoshop'), 'type'              => 'title', 'desc' => '' ),

	array(
		'name'           => __('Company Name','jigoshop'),
		'desc'           => '',
		'tip'            => __('Setting your company name will enable us to print it out on your invoice emails. Leave blank to disable.','jigoshop'),
		'id'             => 'jigoshop_company_name',
		'css'            => 'width:300px;',
		'type'           => 'text',
		'std'            => ''
	),

	array(
		'name'           => __('Tax Registration Number','jigoshop'),
		'desc'           => 'Add your tax registration label before the registration number and it will be printed as well. eg. <code>VAT Number: 88888888</code>',
		'tip'            => __('Setting your tax number will enable us to print it out on your invoice emails. Leave blank to disable.','jigoshop'),
		'id'             => 'jigoshop_tax_number',
		'css'            => 'width:300px;',
		'type'           => 'text',
		'std'            => ''
	),

	array(
		'name'           => __('Address Line1','jigoshop'),
		'desc'           => '',
		'tip'            => __('Setting your address will enable us to print it out on your invoice emails. Leave blank to disable.','jigoshop'),
		'id'             => 'jigoshop_address_line1',
		'css'            => 'width:300px;',
		'type'           => 'text',
		'std'            => ''
	),

	array(
		'name'           => __('Address Line2','jigoshop'),
		'desc'           => '',
		'tip'            => __('If address line1 is not set, address line2 will not display even if you put a value in it. Setting your address will enable us to print it out on your invoice emails. Leave blank to disable.','jigoshop'),
		'id'             => 'jigoshop_address_line2',
		'css'            => 'width:300px;',
		'type'           => 'text',
		'std'            => ''
	),

	array(
		'name'           => __('Company Phone','jigoshop'),
		'desc'           => '',
		'tip'            => __('Setting your company phone number will enable us to print it out on your invoice emails. Leave blank to disable.','jigoshop'),
		'id'             => 'jigoshop_company_phone',
		'css'            => 'width:300px;',
		'type'           => 'text',
		'std'            => ''
	),

	array(
		'name'           => __('Company Email','jigoshop'),
		'desc'           => '',
		'tip'            => __('Setting your company email will enable us to print it out on your invoice emails. Leave blank to disable.','jigoshop'),
		'id'             => 'jigoshop_company_email',
		'css'            => 'width:300px;',
		'type'           => 'text',
		'std'            => ''
	),

	array( 'name'        => __('Integration', 'jigoshop'), 'type'            => 'title', 'desc' => '' ),

	array(
		'name'           => __('ShareThis Publisher ID','jigoshop'),
		'desc'           => __("Enter your <a href='http://sharethis.com/account/'>ShareThis publisher ID</a> to show ShareThis on product pages.",'jigoshop'),
		'tip'            => __('ShareThis is a small social sharing widget for posting links on popular sites such as Twitter and Facebook.','jigoshop'),
		'id'             => 'jigoshop_sharethis',
		'css'            => 'width:300px;',
		'type'           => 'text',
		'std'            => ''
	),

	array(
		'name'           => __('Google Analytics ID', 'jigoshop'),
		'desc'           => __('Log into your Google Analytics account to find your ID. e.g. <code>UA-XXXXXXX-X</code>', 'jigoshop'),
		'id'             => 'jigoshop_ga_id',
		'type'           => 'text',
		'css'            => 'min-width:300px;',
	),

	array(
		'name'           => __('Enable Google eCommerce', 'jigoshop'),
		'tip'            => __('Add Google Analytics eCommerce tracking code upon successful orders', 'jigoshop'),
		'desc'           => __('<a href="//support.google.com/analytics/bin/answer.py?hl=en&answer=1009612" target="_TOP">Learn how to enable</a> eCommerce tracking for your Google Analytics account.', 'jigoshop'),
		'id'             => 'jigoshop_ga_ecommerce_tracking_enabled',
		'type'           => 'checkbox',
	),

	array( 'type'        => 'tabend'),

	array( 'type'        => 'tab', 'tabname'                                 => __('Pages', 'jigoshop') ),

	array( 'name'        => __('Permalinks',      'jigoshop'), 'type'        => 'title','desc' => '', 'id' => '' ),

	array(
		'name'           => __('Prepend options','jigoshop'),
		'desc'           => __('Prepend shop categories / tags with base page','jigoshop'),
		'tip'            => __('This will only apply to tags &amp; categories.<br/>Enabled: http://yoursite.com / product_category / YourCategory<br/>Disabled: http://yoursite.com / base_page / product_category / YourCategory', 'jigoshop'),
		'id'             => 'jigoshop_prepend_shop_page_to_urls',
		'std'            => 'no',
		'type'           => 'checkbox',
	),

	array(
		'desc'           => __('Prepend product permalinks with shop base page','jigoshop'),
		'id'             => 'jigoshop_prepend_shop_page_to_product',
		'std'            => 'no',
		'type'           => 'checkbox',
	),

	array(
		'desc'           => __('Prepend product permalinks with product category','jigoshop'),
		'id'             => 'jigoshop_prepend_category_to_product',
		'std'            => 'no',
		'type'           => 'checkbox',
	),

	array(
		'name'           => __('Slug variables','jigoshop'),
		'desc'           => 'Product category slug',
		'tip'            => __('Slug displayed in product category URLs. Leave blank to use default "product-category"', 'jigoshop'),
		'id'             => 'jigoshop_product_category_slug',
		'std'            => 'product-category',
		'css'            => 'width:130px;',
		'type'           => 'text',
		'group'          => true
	),

	array(
		'desc'           => __('Product tag slug','jigoshop'),
		'tip'            => __('Slug displayed in product tag URLs. Leave blank to use default "product-tag"', 'jigoshop'),
		'id'             => 'jigoshop_product_tag_slug',
		'std'            => 'product-tag',
		'css'            => 'width:130px;',
		'type'           => 'text',
		'group'          => true
	),

	array( 'name'        => __('Shop page configuration', 'jigoshop'), 'type' => 'title', 'desc' => '' ),

	array(
		'name'           => __('Cart Page','jigoshop'),
		'desc'           => __('Shortcode to place on page: <code>[jigoshop_cart]</code>','jigoshop'),
		'tip'            => '',
		'id'             => 'jigoshop_cart_page_id',
		'css'            => 'min-width:50px;',
		'type'           => 'single_select_page',
		'std'            => ''
	),

	array(
		'name'           => __('Checkout Page','jigoshop'),
		'desc'           => __('Shortcode to place on page: <code>[jigoshop_checkout]</code>','jigoshop'),
		'tip'            => '',
		'id'             => 'jigoshop_checkout_page_id',
		'css'            => 'min-width:50px;',
		'type'           => 'single_select_page',
		'std'            => ''
	),

	array(
		'name'           => __('Pay Page','jigoshop'),
		'desc'           => __('Shortcode to place on page: <code>[jigoshop_pay]</code><br/>Default parent page: Checkout','jigoshop'),
		'tip'            => '',
		'id'             => 'jigoshop_pay_page_id',
		'css'            => 'min-width:50px;',
		'type'           => 'single_select_page',
		'std'            => ''
	),

	array(
		'name'           => __('Thanks Page','jigoshop'),
		'desc'           => __('Shortcode to place on page: <code>[jigoshop_thankyou]</code><br/>Default parent page: Checkout','jigoshop'),
		'tip'            => '',
		'id'             => 'jigoshop_thanks_page_id',
		'css'            => 'min-width:50px;',
		'type'           => 'single_select_page',
		'std'            => ''
	),

	array(
		'name'           => __('My Account Page','jigoshop'),
		'desc'           => __('Shortcode to place on page: <code>[jigoshop_my_account]</code>','jigoshop'),
		'tip'            => '',
		'id'             => 'jigoshop_myaccount_page_id',
		'css'            => 'min-width:50px;',
		'type'           => 'single_select_page',
		'std'            => ''
	),

	array(
		'name'          => __('Edit Address Page','jigoshop'),
		'desc'          => __('Shortcode to place on page: <code>[jigoshop_edit_address]</code><br/>Default parent page: My Account','jigoshop'),
		'tip'           => '',
		'id'            => 'jigoshop_edit_address_page_id',
		'css'           => 'min-width:50px;',
		'type'          => 'single_select_page',
		'std'           => ''
	),

	array(
		'name'          => __('View Order Page','jigoshop'),
		'desc'          => __('Shortcode to place on page: <code>[jigoshop_view_order]</code><br/>Default parent page: My Account','jigoshop'),
		'tip'           => '',
		'id'            => 'jigoshop_view_order_page_id',
		'css'           => 'min-width:50px;',
		'type'          => 'single_select_page',
		'std'           => ''
	),

	array(
		'name'          => __('Change Password Page','jigoshop'),
		'desc'          => __('Shortcode to place on page: <code>[jigoshop_change_password]</code><br/>Default parent page: My Account','jigoshop'),
		'tip'           => '',
		'id'            => 'jigoshop_change_password_page_id',
		'css'           => 'min-width:50px;',
		'type'          => 'single_select_page',
		'std'           => ''
	),

	array(
		'name'          => __('Track Order Page','jigoshop'),
		'desc'          => __('Shortcode to place on page: <code>[jigoshop_order_tracking]</code>','jigoshop'),
		'tip'           => '',
		'id'            => 'jigoshop_track_order_page_id',
		'css'           => 'min-width:50px;',
		'type'          => 'single_select_page',
		'std'           => ''
	),

	array(
		'name'          => __('Terms Page', 'jigoshop'),
		'desc'          => __('If you define a &#34;Terms&#34; page the customer will be asked to accept it before allowing them to place their order.', 'jigoshop'),
		'tip'           => '',
		'id'            => 'jigoshop_terms_page_id',
		'css'           => 'min-width:50px;',
		'std'           => '',
		'type'          => 'single_select_page',
		'args'          => 'show_option_none=' . __('None', 'jigoshop'),
	),

	array( 'type'       => 'tabend'),

	array( 'type'       => 'tab', 'tabname'                         => __('Catalog &amp; Pricing', 'jigoshop') ),

	array( 'name'       => __('Catalog Options', 'jigoshop'), 'type' => 'title','desc' => '', 'id' => '' ),


	array(
		'name'          => __('Catalog base page','jigoshop'),
		'desc'          => '',
		'tip'           => __('This sets the base page of your shop. You should not change this value once you have launched your site otherwise you risk breaking urls of other sites pointing to yours, etc.','jigoshop'),
		'id'            => 'jigoshop_shop_page_id',
		'css'           => 'min-width:50px;',
		'type'          => 'single_select_page',
		'std'           => ''
	),
	array(
		'name'          => __('Shop redirection page','jigoshop'),
		'desc'          => '',
		'tip'           => __('This will point users to the page you set for buttons like `Return to shop` or `Continue Shopping`.','jigoshop'),
		'id'            => 'jigoshop_shop_redirect_page_id',
		'css'           => 'min-width:50px;',
		'type'          => 'single_select_page',
		'std'           => ''
	),

	array(
		'name'          => __('Sort products in catalog by','jigoshop'),
		'desc'          => '',
		'tip'           => __('Determines the display sort order of products for the Shop, Categories, and Tag pages.','jigoshop'),
		'id'            => 'jigoshop_catalog_sort_orderby',
		'std'           => 'post_date',
		'type'          => 'radio',
		'options'       => array(
			'post_date' => __('Creation Date', 'jigoshop'),
			'title'     => __('Product Title', 'jigoshop'),
			'menu_order'=> __('Product Post Order', 'jigoshop')
		)
	),

	array(
		'name'          => __('Catalog sort direction','jigoshop'),
		'desc'          => '',
		'tip'           => __('Determines whether the catalog sort orderby is ascending or descending.','jigoshop'),
		'id'            => 'jigoshop_catalog_sort_direction',
		'std'           => 'asc',
		'type'          => 'radio',
		'options'       => array(
			'asc'       => __('Ascending', 'jigoshop'),
			'desc'      => __('Descending', 'jigoshop')
		)
	),

	array(
		'name'          => __('Catalog products display','jigoshop'),
		'desc'          => __('Per row','jigoshop'),
		'tip'           => __('Determines how many products to show on one display row for Shop, Category and Tag pages. Default = 3.','jigoshop'),
		'id'            => 'jigoshop_catalog_columns',
		'css'           => 'width:60px;',
		'std'           => '3',
		'type'          => 'number',
		'restrict'      => array( 'min' => 0 ),
		'group'         => true
	),

	array(
		'desc'          => __('Per page','jigoshop'),
		'tip'           => __('Determines how many products to display on Shop, Category and Tag pages before needing next and previous page navigation. Default = 12.','jigoshop'),
		'id'            => 'jigoshop_catalog_per_page',
		'css'           => 'width:60px;',
		'std'           => '12',
		'type'          => 'number',
		'restrict'      => array( 'min' => 0 ),
		'group'         => true
	),

	array( 'name'       => __('Pricing Options', 'jigoshop'), 'type' => 'title','desc' => '', 'id' => '' ),

	array(
		'name'          => __('Currency', 'jigoshop'),
		'desc'          => sprintf( __("This controls what currency prices are listed at in the catalog, and which currency PayPal, and other gateways, will take payments in. See the list of supported <a target='_new' href='%s'>PayPal currencies</a>.", 'jigoshop'), 'https://www.paypal.com/cgi-bin/webscr?cmd=p/sell/mc/mc_intro-outside' ),
		'tip'           => '',
		'id'            => 'jigoshop_currency',
		'css'           => 'min-width:200px;',
		'std'           => 'GBP',
		'type'          => 'select',
		'options'       => apply_filters('jigoshop_currencies', array(
			'AED' => __('United Arab Emirates dirham (&#1583;&#46;&#1573;)', 'jigoshop'),
			'AUD' => __('Australian Dollar (&#36;)'                        , 'jigoshop'),
			'BRL' => __('Brazilian Real (&#82;&#36;)'                      , 'jigoshop'),
			'CAD' => __('Canadian Dollar (&#36;)'                          , 'jigoshop'),
			'CHF' => __('Swiss Franc (SFr.)'                               , 'jigoshop'),
			'CNY' => __('Chinese yuan (&#165;)'                            , 'jigoshop'),
			'CZK' => __('Czech Koruna (&#75;&#269;)'                       , 'jigoshop'),
			'DKK' => __('Danish Krone (kr)'                                , 'jigoshop'),
			'EUR' => __('Euro (&euro;)'                                    , 'jigoshop'),
			'GBP' => __('Pounds Sterling (&pound;)'                        , 'jigoshop'),
			'HKD' => __('Hong Kong Dollar (&#36;)'                         , 'jigoshop'),
			'HRK' => __('Croatian Kuna (&#107;&#110;)'                     , 'jigoshop'),
			'HUF' => __('Hungarian Forint (&#70;&#116;)'                   , 'jigoshop'),
			'IDR' => __('Indonesia Rupiah (&#82;&#112;)'                   , 'jigoshop'),
			'ILS' => __('Israeli Shekel (&#8362;)'                         , 'jigoshop'),
			'INR' => __('Indian Rupee (&#8360;)'                           , 'jigoshop'),
			'JPY' => __('Japanese Yen (&yen;)'                             , 'jigoshop'),
			'MXN' => __('Mexican Peso (&#36;)'                             , 'jigoshop'),
			'MYR' => __('Malaysian Ringgits (RM)'                          , 'jigoshop'),
			'NGN' => __('Nigerian Naira (&#8358;)'                         , 'jigoshop'),
			'NOK' => __('Norwegian Krone (kr)'                             , 'jigoshop'),
			'NZD' => __('New Zealand Dollar (&#36;)'                       , 'jigoshop'),
			'PHP' => __('Philippine Pesos (&#8369;)'                       , 'jigoshop'),
			'PLN' => __('Polish Zloty (&#122;&#322;)'                      , 'jigoshop'),
			'RON' => __('Romanian New Leu (&#108;&#101;&#105;)'            , 'jigoshop'),
			'RUB' => __('Russian Ruble (&#1088;&#1091;&#1073;)'            , 'jigoshop'),
			'SEK' => __('Swedish Krona (kr)'                               , 'jigoshop'),
			'SGD' => __('Singapore Dollar (&#36;)'                         , 'jigoshop'),
			'THB' => __('Thai Baht (&#3647;)'                              , 'jigoshop'),
			'TRY' => __('Turkish Lira (&#8356;)'                           , 'jigoshop'),
			'TWD' => __('Taiwan New Dollar (&#36;)'                        , 'jigoshop'),
			'USD' => __('US Dollar (&#36;)'                                , 'jigoshop'),
			'ZAR' => __('South African rand (R)'                           , 'jigoshop')
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
			'left'             => __(get_jigoshop_currency_symbol() . '0'                                     . get_option('jigoshop_price_decimal_sep'). '00'                                  , 'jigoshop'),
			'left_space'       => __(get_jigoshop_currency_symbol() . ' 0'                                    . get_option('jigoshop_price_decimal_sep'). '00'                                  , 'jigoshop'),
			'right'            => __('0'                            . get_option('jigoshop_price_decimal_sep'). '00'                                    . get_jigoshop_currency_symbol()        , 'jigoshop'),
			'right_space'      => __('0'                            . get_option('jigoshop_price_decimal_sep'). '00 '                                   . get_jigoshop_currency_symbol()        , 'jigoshop'),
			'left_code'        => __(get_option('jigoshop_currency'). '0'                                     . get_option('jigoshop_price_decimal_sep'). '00'                                  , 'jigoshop'),
			'left_code_space'  => __(get_option('jigoshop_currency'). ' 0'                                    . get_option('jigoshop_price_decimal_sep'). '00'                                  , 'jigoshop'),
			'right_code'       => __('0'                            . get_option('jigoshop_price_decimal_sep'). '00'                                    . get_option('jigoshop_currency')       , 'jigoshop'),
			'right_code_space' => __('0'                            . get_option('jigoshop_price_decimal_sep'). '00 '                                   . get_option('jigoshop_currency')       , 'jigoshop'),
			'symbol_code'      => __(get_jigoshop_currency_symbol() . '0'                                     . get_option('jigoshop_price_decimal_sep'). '00' . get_option('jigoshop_currency'), 'jigoshop'),
			'symbol_code_space'=> __(get_jigoshop_currency_symbol() . ' 0'                                    . get_option('jigoshop_price_decimal_sep'). '00 '. get_option('jigoshop_currency'), 'jigoshop'),
			'code_symbol'      => __(get_option('jigoshop_currency'). '0'                                     . get_option('jigoshop_price_decimal_sep'). '00' . get_jigoshop_currency_symbol() , 'jigoshop'),
			'code_symbol_space'=> __(get_option('jigoshop_currency'). ' 0'                                    . get_option('jigoshop_price_decimal_sep'). '00 '. get_jigoshop_currency_symbol() , 'jigoshop'),
		)
	),

	array(
		'name'         => __('Price Separators', 'jigoshop'),
		'desc'         => __('Thousand separator', 'jigoshop'),
		'id'           => 'jigoshop_price_thousand_sep',
		'css'          => 'width:30px;',
		'std'          => ',',
		'type'         => 'text',
		'group'         => true
	),

	array(
		'desc'         => __('Decimal separator', 'jigoshop'),
		'id'           => 'jigoshop_price_decimal_sep',
		'css'          => 'width:30px;',
		'std'          => '.',
		'type'         => 'text',
		'group'         => true
	),

	array(
		'desc'         => __('Number of decimals', 'jigoshop'),
		'id'           => 'jigoshop_price_num_decimals',
		'css'          => 'width:30px;',
		'std'          => '2',
		'type'         => 'number',
		'restrict'      => array( 'min' => 0 ),
		'group'        => true
	),

	array( 'type'      => 'tabend'),

	array( 'type'      => 'tab', 'tabname'                            => __('Images', 'jigoshop') ),

	array( 'name'      => __('Image Options', 'jigoshop'), 'type'     => 'title', 'desc' => sprintf(__('<p>Changing any of these settings will affect the dimensions of images used in your Shop. After changing these settings you may need to <a href="%s">regenerate your thumbnails</a>.</p>
																										<p>Crop: Leave unchecked to set the image size by resizing the image proportionally (that is, without distorting it). Leave checked to set the image size by hard cropping the image (either from the sides, or from the top and bottom).</p>
																										<p><strong>Note:</strong> Your images may not display in the size you choose below. This is because they may still be affected by CSS styles, that is, your theme.', 'jigoshop'), 'http://wordpress.org/extend/plugins/regenerate-thumbnails/'), 'id' => '' ),

	array(
		'name'         => __('Tiny Images','jigoshop'),
		'desc'         => __('Cart, Checkout, Orders and Widgets','jigoshop'),
		'id'           => 'jigoshop_shop_tiny',
		'type'         => 'image_size',
		'std'          => 36,
		'placeholder'  => 36
	),

	array(
		'name'         => __('Thumbnail Images','jigoshop'),
		'desc'         => __('Single Product page extra images.','jigoshop'),
		'id'           => 'jigoshop_shop_thumbnail',
		'type'         => 'image_size',
		'std'          => 90,
		'placeholder'  => 90
	),

	array(
		'name'         => __( 'Catalog Images', 'jigoshop' ),
		'desc'         => __('Shop, Categories, Tags, and Related Products.', 'jigoshop'),
		'id'           => 'jigoshop_shop_small',
		'type'         => 'image_size',
		'std'          => 150,
		'placeholder'  => 150
	),

	array(
		'name'         => __('Large Images','jigoshop'),
		'desc'         => __('Single Product pages','jigoshop'),
		'id'           => 'jigoshop_shop_large',
		'type'         => 'image_size',
		'std'          => 300,
		'placeholder'  => 300
	),

	array( 'type'      => 'tabend'),

	array( 'type'      => 'tab', 'tabname'                            => __('Coupons', 'jigoshop') ),

	array( 'name'      => __('Coupon Information', 'jigoshop'), 'type' => 'title', 'desc' => __('<div>Coupons allow you to give your customers special offers and discounts. </div>','jigoshop') ),

	array(
		'name'         => __('Coupons','jigoshop'),
		'desc'         => __('All fields are required.','jigoshop'),
		'id'           => 'jigoshop_coupons',
		'css'          => 'min-width:50px;',
		'type'         => 'coupons',
		'std'          => ''
	),

	array( 'type'      => 'tabend'),

	array( 'type'      => 'tab', 'tabname'                            => __('Products &amp; Inventory', 'jigoshop') ),

	array( 'name'      => __('Product Options', 'jigoshop'), 'type'   => 'title', 'desc' => '' ),

	array(
		'name'         => __('Product fields','jigoshop'),
		'desc'         => __('Enable SKU','jigoshop'),
		'tip'          => __('Turning off the SKU field will give products an SKU of their post id.','jigoshop'),
		'id'           => 'jigoshop_enable_sku',
		'std'          => 'no',
		'type'         => 'checkbox',
	),

	array(
		'desc'         => __('Enable weight','jigoshop'),
		'tip'          => '',
		'id'           => 'jigoshop_enable_weight',
		'std'          => 'yes',
		'type'         => 'checkbox',
	),

	array(
		'desc'         => __('Enable product dimensions','jigoshop'),
		'tip'          => '',
		'id'           => 'jigoshop_enable_dimensions',
		'std'          => 'yes',
		'type'         => 'checkbox',
	),

	array(
		'name'         => __('Weight Unit', 'jigoshop'),
		'tip'          => __("This controls what unit you will define weights in.", 'jigoshop'),
		'id'           => 'jigoshop_weight_unit',
		'std'          => 'kg',
		'type'         => 'radio',
		'options'      => array(
			'kg'       => __('Kilograms', 'jigoshop'),
			'lbs'      => __('Pounds', 'jigoshop')
		)
	),

	array(
		'name'         => __('Dimensions Unit', 'jigoshop'),
		'tip'          => __("This controls what unit you will define dimensions in.", 'jigoshop'),
		'id'           => 'jigoshop_dimension_unit',
		'std'          => 'cm',
		'type'         => 'radio',
		'options'      => array(
			'cm'       => __('centimeters', 'jigoshop'),
			'in'       => __('inches', 'jigoshop')
		)
	),

	array(
		'name'         => __('Show related products','jigoshop'),
		'desc'         => '',
		'tip'          => __('To show or hide the related products section on a single product page.','jigoshop'),
		'id'           => 'jigoshop_enable_related_products',
		'std'          => 'yes',
		'type'         => 'checkbox',
	),

	array( 'name'      => __('Inventory Options', 'jigoshop'), 'type' => 'title','desc' => '', 'id'                                                                                                                                                                                                                                                                                                                                                            => '' ),

	array(
		'name'         => __('General inventory options','jigoshop'),
		'desc'         => __('Enable product stock','jigoshop'),
		'tip'          => __('If you are not managing stock, turn it off here to disable it in admin and on the front-end. You can manage stock on a per-item basis if you leave this option on.', 'jigoshop'),
		'id'           => 'jigoshop_manage_stock',
		'std'          => 'yes',
		'type'         => 'checkbox',
	),

	array(
		'desc'         => __('Show stock amounts','jigoshop'),
		'tip'          => __('Set to yes to allow customers to view the amount of stock available for a product.', 'jigoshop'),
		'id'           => 'jigoshop_show_stock',
		'std'          => 'yes',
		'type'         => 'checkbox',
	),

	array(
		'desc'         => __('Hide out of stock products','jigoshop'),
		'tip'          => 'When enabled: When the Out of Stock Threshold (above) is reached, the product visibility will be set to hidden so that it will not appear on the Catalog or Shop product lists.',
		'id'           => 'jigoshop_hide_no_stock_product',
		'std'          => 'no',
		'type'         => 'checkbox'
	),

	array(
		'name'         => __('Notifications','jigoshop'),
		'desc'         => __('Notify on low stock','jigoshop'),
		'id'           => 'jigoshop_notify_low_stock',
		'std'          => 'yes',
		'type'         => 'checkbox',
	),

	array(
		'desc'         => __('Low stock threshold','jigoshop'),
		'tip'          => __('You will receive a notification as soon this threshold is hit (if notifications are turned on).', 'jigoshop'),
		'id'           => 'jigoshop_notify_low_stock_amount',
		'css'          => 'width:50px;',
		'type'         => 'number',
		'restrict'     => array( 'min' => 0 ),
		'std'          => '2',
		'group'        => true
	),

	array(
		'desc'         => __('Notify on out of stock','jigoshop'),
		'id'           => 'jigoshop_notify_no_stock',
		'std'          => 'yes',
		'type'         => 'checkbox',
		'group'        => true
	),

	array(
		'desc'         => __('Out of stock threshold','jigoshop'),
		'tip'          => __('You will receive a notification as soon this threshold is hit (if notifications are turned on).', 'jigoshop'),
		'id'           => 'jigoshop_notify_no_stock_amount',
		'css'          => 'width:50px;',
		'type'         => 'number',
		'restrict'     => array( 'min' => 0 ),
		'std'          => '0',
		'group'        => true
	),



	array( 'type'      => 'tabend'),

	array( 'type'      => 'tab', 'tabname'                            => __('Shipping', 'jigoshop') ),

	array( 'name'      => __('Shipping Options', 'jigoshop'), 'type'  => 'title','desc' => '', 'id'                                                                                                                                                                                                                                                                                                                                                            => '' ),

	array(
		'name'         => __('General shipping settings','jigoshop'),
		'desc'         => __('Calculate shipping','jigoshop'),
		'tip'          => __('Only set this to no if you are not shipping items, or items have shipping costs included. If you are not calculating shipping then you can ignore all other tax options.', 'jigoshop'),
		'id'           => 'jigoshop_calc_shipping',
		'std'          => 'yes',
		'type'         => 'checkbox'
	),

	array(
		'desc'         => __('Enable shipping calculator on cart','jigoshop'),
		'tip'          => '',
		'id'           => 'jigoshop_enable_shipping_calc',
		'std'          => 'yes',
		'type'         => 'checkbox',
	),

	array(
		'desc'         => __('Only ship to billing address?','jigoshop'),
		'tip'          => '',
		'id'           => 'jigoshop_ship_to_billing_address_only',
		'std'          => 'no',
		'type'         => 'checkbox',
	),

	array( 'type'      => 'shipping_options'),

	array( 'type'      => 'tabend'),

	array( 'type'      => 'tab', 'tabname'                            => __('Tax', 'jigoshop') ),

	array( 'name'      => __('Tax Options', 'jigoshop'), 'type'       => 'title','desc' => '', 'id'                                                                                                                                                                                                                                                                                                                                                            => '' ),

	array(
		'name'         => __('General tax options','jigoshop'),
		'desc'         => __('Enable tax calculation','jigoshop'),
		'tip'          => __('Only disable this if you are exclusively selling non-taxable items. If you are not calculating taxes then you can ignore all other tax options.', 'jigoshop'),
		'id'           => 'jigoshop_calc_taxes',
		'std'          => 'yes',
		'type'         => 'checkbox',
	),

	array(
		'desc'         => __('Apply Taxes After Coupon','jigoshop'),
		'tip'          => __('If yes, taxes get applied after coupons. When no, taxes get applied before coupons.','jigoshop'),
		'id'           => 'jigoshop_tax_after_coupon',
		'std'          => 'yes',
		'type'         => 'checkbox',
	),

	array(
		'desc'         => __('Catalog Prices include tax?','jigoshop'),
		'tip'          => __('If prices include tax then tax calculations will work backwards.','jigoshop'),
		'id'           => 'jigoshop_prices_include_tax',
		'std'          => 'yes',
		'type'         => 'checkbox',
	),

	array(
		'name'         => __('Cart total displays','jigoshop'),
		'desc'         => '',
		'tip'          => __('Should the subtotal be shown including or excluding tax on the frontend?','jigoshop'),
		'id'           => 'jigoshop_display_totals_tax',
		'std'          => 'excluding',
		'type'         => 'radio',
		'options'      => array(
			'including' => __('price including tax', 'jigoshop'),
			'excluding' => __('price excluding tax', 'jigoshop')
		)
	),

	array(
		'name'         => __('Additional Tax classes','jigoshop'),
		'desc'         => __('List 1 per line. This is in addition to the default <em>Standard Rate</em>.','jigoshop'),
		'tip'          => __('List product and shipping tax classes here, e.g. Zero Tax, Reduced Rate.','jigoshop'),
		'id'           => 'jigoshop_tax_classes',
		'css'          => 'width:100%; height: 75px;',
		'type'         => 'textarea',
		'std'          => sprintf( __( 'Reduced Rate%sZero Rate', 'jigoshop' ), PHP_EOL )
	),

	array(
		'name'         => __('Tax rates','jigoshop'),
		'desc'         => __('All fields are required.','jigoshop'),
		'tip'          => __('To avoid rounding errors, insert tax rates with 4 decimal places.','jigoshop'),
		'id'           => 'jigoshop_tax_rates',
		'css'          => 'min-width:50px;',
		'type'         => 'tax_rates',
		'std'          => ''
	),

	array( 'type'      => 'tabend'),

	array( 'type'      => 'tab', 'tabname'                            => __('Payment Gateways', 'jigoshop') ),

	array( 'type'      => 'gateway_options'),

	array( 'type'      => 'tabend')

) );
