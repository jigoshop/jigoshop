<?php
/**
 * Jigoshop_Options class contains all WordPress options used within Jigoshop
 *
 * DISCLAIMER
 *
 * Do not edit or add directly to this file if you wish to upgrade Jigoshop to newer
 * versions in the future. If you wish to customise Jigoshop core for your needs,
 * please use our GitHub repository to publish essential changes for consideration.
 *
 * @package		Jigoshop
 * @category	Core
 * @author		Jigowatt
 * @copyright	Copyright (c) 2011-2012 Jigowatt Ltd.
 * @license		http://jigoshop.com/license/commercial-edition
 */

class Jigoshop_Options {
	
	private static $default_options;
	private static $current_options;
	
	
	/**
	 * Instantiates a new Options object
	 *
	 * @param   none
	 * @return  Void
	 *
	 * @since	1.2
	 */	
	public function __construct() {
		
		self::$current_options = get_option( JIGOSHOP_OPTIONS );	// load existing from database, false if none
		
		if ( false === self::$current_options ) {
			
			$current_options = array();
	
			foreach ( self::get_default_options() as $setting ) :
				if ( isset( $setting['id'] )) :
					$current_options[$setting['id']] = $setting['std'];
				endif;
			endforeach;
			
			self::$current_options = $current_options;
			update_option( JIGOSHOP_OPTIONS, $current_options );
		}
		
	}
	
	
	/**
	 * Updates the database with the current options
	 *
	 * At various times during a page load, options can be set, or added.
	 * We will flush them all out on the WordPress 'shutdown' action hook.
	 *
	 * If options don't exist (fresh install), they are created with default 'true' for WP autoload
	 *
	 * @since	1.2
	 */	
	public static function update_options() {
		update_option( JIGOSHOP_OPTIONS, self::$current_options );
	}
	
	
	/**
	 * Adds a named option to our collection
	 *
	 * Will do nothing if option already exists to match WordPress behaviour
	 * Use 'set_option' to actually set an existing option
	 *
	 * @param   string	the name of the option to add
	 * @param   mixed	the value to set if the option doesn't exist
	 *
	 * @since	1.2
	 */	
	public static function add_option( $name, $value ) {
		self::get_current_options();
		if ( ! isset( self::$current_options[$name] )) {
			self::$current_options[$name] = $value;
			add_action( 'shutdown', array( __CLASS__, 'update_options' ));
		}
	}
	
	
	/**
	 * Returns a named Jigoshop option
	 *
	 * @param   string	the name of the option to retrieve
	 * @param   mixed	the value to return if the option doesn't exist
	 * @return  mixed	the value of the option, null if no $default and doesn't exist
	 *
	 * @since	1.2
	 */	
	public static function get_option( $name, $default = null ) {
		if ( isset( self::$current_options[$name] )) return self::$current_options[$name];
		else if ( isset( $default )) return $default;
		else return null;
	}
	
	
	/**
	 * Sets a named Jigoshop option
	 *
	 * @param   string	the name of the option to set
	 * @param	mixed	the value to set
	 *
	 * @since	1.2
	 */	
	public static function set_option( $name, $value ) {
		self::get_current_options();
		if ( isset( $name )) {
			self::$current_options[$name] = $value;
			add_action( 'shutdown', array( __CLASS__, 'update_options' ));
		}
	}
	
	
	/**
	 * Deletes a named Jigoshop option
	 *
	 * @param   string	the name of the option to delete
	 * @return	bool	true for successful completion if option found, false otherwise
	 *
	 * @since	1.2
	 */	
	public static function delete_option( $name ) {
		self::get_current_options();
		if ( isset( $name )) {
			unset( self::$current_options[$name] );
			add_action( 'shutdown', array( __CLASS__, 'update_options' ));
			return true;
		}
		return false;
	}
	
	
	/**
	 * Install additional default options for parsing
	 * Shipping methods and Payment gateways would use this
	 *
	 * @param	string	The name of the Tab ('heading') to install onto
	 * @param	array	The array of options to install
	 *
	 * @since	1.2
	 */	
	public static function install_external_options( $tab, $options ) {
		$our_options = self::get_default_options();
		$first_index = -1;
		$second_index = -1;
		foreach ( $our_options as $index => $option ) {
			if ( $option['type'] <> 'heading' ) continue;
			if ( $option['name'] == $tab ) {
				$first_index = $index;
				continue;
			}
			if ( $first_index >= 0 ) {
				$second_index = $index;
				break;
			}
		}
		if ( $second_index < 0 ) $second_index = count( $our_options );
		
		/*** get the start of the array ***/
		$start = array_slice( $our_options, 0, $second_index ); 
		/*** get the end of the array ***/
		$end = array_slice( $our_options, $second_index );
		/*** add the new elements to the array ***/
		foreach ( $options as $option ) {
		 	if ( isset( $option['id'] ) ) {
		 		self::add_option( $option['id'], $option['std'] );
		 	}
		 	$start[] = $option;
		}
		/*** glue them back together ***/
		self::$default_options = array_merge( $start, $end );
 	}
	
	
	/**
	 * Return the Jigoshop current options
	 *
	 * @param   none
	 * @return  array	the entire current options array is returned
	 *
	 * @since	1.2
	 */	
	public static function get_current_options() {
		if ( empty( self::$current_options )) {
			if ( empty( self::$default_options )) self::set_default_options();
			else self::set_current_options( self::$default_options );;
		}
		return self::$current_options;
	}
	
	
	/**
	 * Sets the entire Jigoshop current options
	 *
	 * @param   array	an array containing all the current Jigoshop option => value pairs to use
	 * @return  Void
	 *
	 * @since	1.2
	 */	
	public static function set_current_options( $options ) {
		self::$current_options = $options;
		add_action( 'shutdown', array( __CLASS__, 'update_options' ));
	}
	
	
	/**
	 * Return the Jigoshop default options
	 *
	 * @param   none
	 * @return  array	the entire default options array is returned
	 *
	 * @since	1.2
	 */	
	public static function get_default_options() {
		if ( empty( self::$default_options )) self::set_default_options();
		return self::$default_options;
	}
	
	
	/**
	 * Sets the Jigoshop default options
	 *
	 * This will create the default options array. Extensions may install options of the same format into this.
	 *
	 * @param   none
	 * @return  Void
	 *
	 * @since	1.2
	 *
	 *  ====================
	 *
	 * Supported Option Types:
	 *      text                    - standard text input (display size 20 chars)
	 *      longtext                - same as text (display size 80 chars)
	 *      email                   - same as text (display size 40 chars)
	 *      textarea                - same as text (display size 4 rows, 60 cols)
	 *      checkbox                - true or false option type
	 *      multicheck              - option grouping allows multiple options for selection (horizontal or vertical display)
	 *      select                  - standard select option with pre-defined choices
	 *      radio                   - option grouping allowing one option for selection (horizontal or vertical display)
	 *      single_select_page      - select that lists all available WordPress pages with a 'None' choice as well
	 *      single_select_country   - select allowing a single choice of all Jigoshop defined countries
	 *      multi_select_countries  - multicheck allowing multiple choices of all Jigoshop defined countries
	 *      user_defined            - a user installed option type, must provide display and option update callbacks
	 *
	 *  ====================
	 *
	 *  The Options array uses Tabs for display and each tab begins with a 'heading' option type
	 *  Each Tab Heading may be optionally divided into sections defined by a 'title' option type
	 *  A Payment Gateway for example, would install itself into a 'heading' and provide a section 'title' with options
	 *  List each option sequentially for display under each 'title' or 'heading' option type
	 *
	 *  Each Option may have any or all of the following items: (for an option, 'id' is MANDATORY and should be unique)
			'tab'           => '',                      - calculated based on position in array
			'section'       => '',                      - calculated based on position in array
			'id'            => null,                    - required
			'type'          => '',                      - required
			'name'          => __( '', 'jigoshop' ),    - used for Option title in Admin display
			'desc'          => __( '', 'jigoshop' ),    - option descriptive information (wrap in <p> tags)
			'tip'           => __( '', 'jigoshop' ),    - a pop-up tool tip providing help information
			'std'           => '',                      - default value for the option
			'choices'       => array(),                 - for selects, radios, etc.
			'class'         => '',                      - any special CSS classes to assign to the options display
			'display'       => null,        - call back function for 'user_defined' - array( $this, 'function_name' )
			'update'        => null,        - call back function for 'user_defined' - array( $this, 'function_name' )
			'extra'         => null,                    - for display and verification - array( 'horizontal' )
	 *
	 */	
	public static function set_default_options() {
		
		self::$default_options = array();
		
		/**
		 * General Tab
		 *------------------------------------------------------------------------------------------
		*/
		self::$default_options[] = array( 'type' => 'heading', 'name' => __('General', 'jigoshop') );
		
		self::$default_options[] = array( 'name' => __('General Options', 'jigoshop'), 'type' => 'title', 'desc' => '' );
		
		self::$default_options[] = array(
			'name'		=> __('Demo store','jigoshop'),
			'desc' 		=> '',
			'tip' 		=> __('Enable this option to show a banner at the top of every page stating this shop is currently in testing mode.','jigoshop'),
			'id' 		=> 'jigoshop_demo_store',
			'std' 		=> 'no',
			'type' 		=> 'radio',
			'choices'	=> array(
				'no'			=> __('No', 'jigoshop'),
				'yes'			=> __('Yes', 'jigoshop')
			)
		);
		
		self::$default_options[] = array(
			'name'		=> __('Send Jigoshop emails from','jigoshop'),
			'desc' 		=> '',
			'tip' 		=> __('The email used to send all Jigoshop related emails, such as order confirmations and notices.','jigoshop'),
			'id' 		=> 'jigoshop_email',
			'type' 		=> 'email',
			'std' 		=> get_option('admin_email')
		);
		
		self::$default_options[] = array(
			'name'		=> __('Base Country/Region','jigoshop'),
			'desc' 		=> '',
			'tip' 		=> __('This is the base country for your business. Tax rates will be based on this country.','jigoshop'),
			'id' 		=> 'jigoshop_default_country',
			'std' 		=> 'GB',
			'type' 		=> 'single_select_country'
		);
		
		self::$default_options[] = array(
			'name'		=> __('Allowed Countries','jigoshop'),
			'desc' 		=> '',
			'tip' 		=> __('These are countries that you are willing to ship to.','jigoshop'),
			'id' 		=> 'jigoshop_allowed_countries',
			'std' 		=> 'all',
			'type' 		=> 'select',
			'choices'	=> array(
				'all'			=> __('All Countries', 'jigoshop'),
				'specific'		=> __('Specific Countries', 'jigoshop')
			)
		);
		
		self::$default_options[] = array(
			'name'		=> __('Specific Countries','jigoshop'),
			'desc' 		=> '',
			'tip' 		=> '',
			'id' 		=> 'jigoshop_specific_allowed_countries',
			'std' 		=> '',
			'type' 		=> 'multi_select_countries'
		);
		
		self::$default_options[] = array(
			'name'		=> __('After adding product to cart','jigoshop'),
			'desc' 		=> '',
			'tip' 		=> __('Define what should happen when a user clicks on &#34;Add to Cart&#34; on any product or page.','jigoshop'),
			'id' 		=> 'jigoshop_redirect_add_to_cart',
			'std' 		=> 'same_page',
			'type' 		=> 'select',
			'choices'	=> array(
				'same_page'		=> __('Stay on the same page', 'jigoshop'),
				'to_checkout'	=> __('Redirect to Checkout', 'jigoshop'),
				'to_cart'		=> __('Redirect to Cart', 'jigoshop'),
			)
		);
		
		self::$default_options[] = array(
			'name'		=> __('Disable Jigoshop frontend.css','jigoshop'),
			'desc' 		=> '',
			'tip' 		=> __('Useful if you want to disable Jigoshop styles and theme it yourself via your theme.','jigoshop'),
			'id' 		=> 'jigoshop_disable_css',
			'std' 		=> 'no',
			'type' 		=> 'radio',
			'choices'	=> array(
				'no'			=> __('No', 'jigoshop'),
				'yes'			=> __('Yes', 'jigoshop')
			)
		);
		
		self::$default_options[] = array(
			'name'		=> __('Disable bundled Fancybox','jigoshop'),
			'desc' 		=> '',
			'tip' 		=> __('Useful if or one of your plugin already loads the Fancybox script and css. But be careful, Jigoshop will still try to open product images using Fancybox.','jigoshop'),
			'id' 		=> 'jigoshop_disable_fancybox',
			'std' 		=> 'no',
			'type' 		=> 'radio',
			'choices'	=> array(
				'no'			=> __('No', 'jigoshop'),
				'yes'			=> __('Yes', 'jigoshop')
			)
		);
		
		self::$default_options[] = array( 'name' => __('Checkout page', 'jigoshop'), 'type' => 'title', 'desc' => '' );
		
		self::$default_options[] = array(
			'name'		=> __('Allow guest purchases','jigoshop'),
			'desc' 		=> '',
			'tip' 		=> __('Setting this to Yes will allow users to checkout without registering or signing up. Otherwise, users must be signed in or must sign up to checkout.','jigoshop'),
			'id' 		=> 'jigoshop_enable_guest_checkout',
			'std' 		=> 'yes',
			'type' 		=> 'radio',
			'choices'	=> array(
				'no'			=> __('No', 'jigoshop'),
				'yes'			=> __('Yes', 'jigoshop')
			)
		);
		
		self::$default_options[] = array(
			'name'		=> __('Show login form','jigoshop'),
			'desc' 		=> '',
			'id' 		=> 'jigoshop_enable_guest_login',
			'std' 		=> 'yes',
			'type' 		=> 'radio',
			'choices'	=> array(
				'no'			=> __('No', 'jigoshop'),
				'yes'			=> __('Yes', 'jigoshop')
			)
		);
		
		self::$default_options[] = array(
			'name'		=> __('Allow registration','jigoshop'),
			'desc' 		=> '',
			'id' 		=> 'jigoshop_enable_signup_form',
			'std' 		=> 'yes',
			'type' 		=> 'radio',
			'choices'	=> array(
				'no'			=> __('No', 'jigoshop'),
				'yes'			=> __('Yes', 'jigoshop')
			)
		);
		
		self::$default_options[] = array(
			'name'		=> __('Force SSL on checkout','jigoshop'),
			'desc' 		=> '',
			'tip' 		=> __('Forcing SSL is recommended. This will load your checkout page with https://. An SSL certificate is <strong>required</strong> if you choose yes. Contact your hosting provider for more information on SSL Certs.','jigoshop'),
			'id' 		=> 'jigoshop_force_ssl_checkout',
			'std' 		=> 'no',
			'type' 		=> 'radio',
			'choices'	=> array(
				'no'			=> __('No', 'jigoshop'),
				'yes'			=> __('Yes', 'jigoshop')
			)
		);
			
		self::$default_options[] = array( 'name' => __('Integration', 'jigoshop'), 'type' => 'title', 'desc' => '' );
		
		self::$default_options[] = array(
			'name'		=> __('ShareThis Publisher ID','jigoshop'),
			'desc' 		=> __("Enter your <a href='http://sharethis.com/account/'>ShareThis publisher ID</a> to show ShareThis on product pages.",'jigoshop'),
			'tip' 		=> __('ShareThis is a small social sharing widget for posting links on popular sites such as Twitter and Facebook.','jigoshop'),
			'id' 		=> 'jigoshop_sharethis',
			'type' 		=> 'text',
			'std' 		=> ''
		);
		
		self::$default_options[] = array(
			'name'		=> __('Google Analytics ID', 'jigoshop'),
			'desc' 		=> __('Log into your Google Analytics account to find your ID. e.g. <code>UA-XXXXXXX-X</code>', 'jigoshop'),
			'id' 		=> 'jigoshop_ga_id',
			'type' 		=> 'text',
			'std' 		=> '',
		);
		
		self::$default_options[] = array(
			'name'		=> __('Enable eCommerce Tracking', 'jigoshop'),
			'tip' 		=> __('Add Google Analytics eCommerce tracking code upon successful orders', 'jigoshop'),
			'desc'		=> __('<a href="//support.google.com/analytics/bin/answer.py?hl=en&answer=1009612">Learn how to enable</a> eCommerce tracking for your Google Analytics account.', 'jigoshop'),
			'id' 		=> 'jigoshop_ga_ecommerce_tracking_enabled',
			'type' 		=> 'radio',
			'std' 		=> 'no',
			'choices'	=> array(
				'no'			=> __('No', 'jigoshop'),
				'yes'			=> __('Yes', 'jigoshop')
			)
		);
		
		/**
		 * Pages Tab
		 *------------------------------------------------------------------------------------------
		*/
		self::$default_options[] = array( 'type' => 'heading', 'name' => __('Pages', 'jigoshop') );
		
		self::$default_options[] = array( 'name' => __('Page configurations', 'jigoshop'), 'type' => 'title', 'desc' => '' );
		
		self::$default_options[] = array(
			'name'		=> __('Jigoshop Checkbox Testing','jigoshop'),
			'desc' 		=> '',
			'tip' 		=> '',
			'id' 		=> 'jigoshop_checkbox_test',
			'std' 		=> false,
			'type' 		=> 'checkbox'
		);
		
		self::$default_options[] = array(
			'name'		=> __('Display Sidebar on these pages:','jigoshop'),
			'desc' 		=> '',
			'tip' 		=> '',
			'id' 		=> 'jigoshop_multicheck_test',
			'type' 		=> 'multicheck',
			"std"		=> array('shop' => true,'category' => false,'single' => true,'cart' => false,'checkout' => true,'account' => true),
			"choices"	=> array(
				"shop"			=> "Shop",
				"category"		=> "Product Categories",
				"single"		=> "Single Products",
				"cart"			=> "Cart",
				"checkout"		=> "Checkout",
				"account"		=> "Account Pages",
			),
			'extra'		=> array( 'horizontal' )
		);
		
		self::$default_options[] = array(
			'name'		=> __('We could also display a multicheck this way:','jigoshop'),
			'desc' 		=> '',
			'tip' 		=> '',
			'id' 		=> 'jigoshop_multicheck_test_vert',
			'type' 		=> 'multicheck',
			"std"		=> array('shop' => false,'category' => true,'single' => false,'cart' => true,'checkout' => false,'account' => true),
			"choices"	=> array(
				"shop"			=> "Shop",
				"category"		=> "Product Categories",
				"single"		=> "Single Products",
				"cart"			=> "Cart",
				"checkout"		=> "Checkout",
				"account"		=> "Account Pages",
			),
			'extra'		=> array( 'vertical' )
		);
		
		self::$default_options[] = array(
			'name'		=> __('Cart Page','jigoshop'),
			'desc' 		=> __('Shortcode to place on page: <code>[jigoshop_cart]</code>','jigoshop'),
			'tip' 		=> '',
			'id' 		=> 'jigoshop_cart_page_id',
			'type' 		=> 'single_select_page',
			'std' 		=> ''
		);
		
		self::$default_options[] = array(
			'name'		=> __('Checkout Page','jigoshop'),
			'desc' 		=> __('Shortcode to place on page: <code>[jigoshop_checkout]</code>','jigoshop'),
			'tip' 		=> '',
			'id' 		=> 'jigoshop_checkout_page_id',
			'type' 		=> 'single_select_page',
			'std' 		=> ''
		);
		
		self::$default_options[] = array(
			'name'		=> __('Pay Page','jigoshop'),
			'desc' 		=> __('Shortcode to place on page: <code>[jigoshop_pay]</code><br/>Default parent page: Checkout','jigoshop'),
			'tip' 		=> '',
			'id' 		=> 'jigoshop_pay_page_id',
			'type' 		=> 'single_select_page',
			'std' 		=> ''
		);
		
		self::$default_options[] = array(
			'name'		=> __('Thanks Page','jigoshop'),
			'desc' 		=> __('Shortcode to place on page: <code>[jigoshop_thankyou]</code><br/>Default parent page: Checkout','jigoshop'),
			'tip' 		=> '',
			'id' 		=> 'jigoshop_thanks_page_id',
			'type' 		=> 'single_select_page',
			'std' 		=> ''
		);
		
		self::$default_options[] = array(
			'name'		=> __('My Account Page','jigoshop'),
			'desc' 		=> __('Shortcode to place on page: <code>[jigoshop_my_account]</code>','jigoshop'),
			'tip' 		=> '',
			'id' 		=> 'jigoshop_myaccount_page_id',
			'type' 		=> 'single_select_page',
			'std' 		=> ''
		);
		
		self::$default_options[] = array(
			'name'		=> __('Edit Address Page','jigoshop'),
			'desc' 		=> __('Shortcode to place on page: <code>[jigoshop_edit_address]</code><br/>Default parent page: My Account','jigoshop'),
			'tip' 		=> '',
			'id' 		=> 'jigoshop_edit_address_page_id',
			'type' 		=> 'single_select_page',
			'std' 		=> ''
		);
		
		self::$default_options[] = array(
			'name'		=> __('View Order Page','jigoshop'),
			'desc' 		=> __('Shortcode to place on page:<code>[jigoshop_view_order]</code><br/>Default parent page: My Account','jigoshop'),
			'tip' 		=> '',
			'id' 		=> 'jigoshop_view_order_page_id',
			'type' 		=> 'single_select_page',
			'std' 		=> ''
		);
		
		self::$default_options[] = array(
			'name'		=> __('Change Password Page','jigoshop'),
			'desc' 		=> __('Shortcode to place on page: <code>[jigoshop_change_password]</code><br/>Default parent page: My Account','jigoshop'),
			'tip' 		=> '',
			'id' 		=> 'jigoshop_change_password_page_id',
			'type' 		=> 'single_select_page',
			'std' 		=> ''
		);
		
		self::$default_options[] = array(
			'name'		=> __('Track Order Page','jigoshop'),
			'desc' 		=> __('Shortcode to place on page: <code>[jigoshop_order_tracking]</code>','jigoshop'),
			'tip' 		=> '',
			'id' 		=> 'jigoshop_track_order_page_id',
			'type' 		=> 'single_select_page',
			'std' 		=> ''
		);
		
		self::$default_options[] = array(
			'name'		=> __('Terms Page', 'jigoshop'),
			'desc' 		=> __('If you define a &#34;Terms&#34; page the customer will be asked to accept it before allowing them to place their order.', 'jigoshop'),
			'tip' 		=> '',
			'id' 		=> 'jigoshop_terms_page_id',
			'std' 		=> '',
			'type' 		=> 'single_select_page',
			'extra'		=> 'show_option_none=' . __('None', 'jigoshop'),
		);
		
		/**
		 * Catalog & Pricing Tab
		 *------------------------------------------------------------------------------------------
		*/
		self::$default_options[] = array( 'type' => 'heading', 'name' => __('Catalog &amp; Pricing', 'jigoshop') );
		
		self::$default_options[] = array( 'name' => __('Catalog Options', 'jigoshop'), 'type' => 'title', 'desc' => '' );
		
		self::$default_options[] = array(
			'name'		=> __('Catalog base page','jigoshop'),
			'desc'		=> '',
			'tip' 		=> __('This sets the base page of your shop. You should not change this value once you have launched your site otherwise you risk breaking urls of other sites pointing to yours, etc.','jigoshop'),
			'id' 		=> 'jigoshop_shop_page_id',
			'type' 		=> 'single_select_page',
			'std' 		=> ''
		);
		
		self::$default_options[] = array(
			'name'		=> __('Shop redirection page','jigoshop'),
			'desc'		=> '',
			'tip' 		=> __('This will point users to the page you set for buttons like `Return to shop` or `Continue Shopping`.','jigoshop'),
			'id' 		=> 'jigoshop_shop_redirect_page_id',
			'type' 		=> 'single_select_page',
			'std' 		=> ''
		);
		
		self::$default_options[] = array(
			'name'		=> __('Prepend links with base page','jigoshop'),
			'desc'		=> '',
			'tip' 		=> __('This will only apply to tags &amp; categories.<br/>Yes: http://yoursite.com / product_category / YourCategory<br/>No: http://yoursite.com / base_page / product_category / YourCategory', 'jigoshop'),
			'id' 		=> 'jigoshop_prepend_shop_page_to_urls',
			'std' 		=> 'no',
			'type' 		=> 'radio',
			'choices'	=> array(
				'no'			=> __('No', 'jigoshop'),
				'yes'			=> __('Yes', 'jigoshop')
			)
		);
		
		self::$default_options[] = array(
			'name'		=> __('Sort products in catalog by','jigoshop'),
			'desc' 		=> '',
			'tip' 		=> __('Determines the display sort order of products for the Shop, Categories, and Tag pages.','jigoshop'),
			'id' 		=> 'jigoshop_catalog_sort_orderby',
			'std' 		=> 'post_date',
			'type' 		=> 'select',
			'choices'	=> array(
				'post_date'		=> __('Creation Date', 'jigoshop'),
				'title'			=> __('Product Title', 'jigoshop'),
				'menu_order'	=> __('Product Post Order', 'jigoshop')
			)
		);
		
		self::$default_options[] = array(
			'name'		=> __('Catalog sort direction','jigoshop'),
			'desc' 		=> '',
			'tip' 		=> __('Determines whether the catalog sort orderby is ascending or descending.','jigoshop'),
			'id' 		=> 'jigoshop_catalog_sort_direction',
			'std' 		=> 'asc',
			'type' 		=> 'select',
			'choices'	=> array(
				'asc'			=> __('Ascending', 'jigoshop'),
				'desc'			=> __('Descending', 'jigoshop')
			)
		);
		
		self::$default_options[] = array(
			'name'		=> __('Catalog products per row','jigoshop'),
			'desc' 		=> __('Default = 3','jigoshop'),
			'tip' 		=> __('Determines how many products to show on one display row for Shop, Category and Tag pages.','jigoshop'),
			'id' 		=> 'jigoshop_catalog_columns',
			'std' 		=> '3',
			'type' 		=> 'text',
		);
		
		self::$default_options[] = array(
			'name'		=> __('Catalog products per page','jigoshop'),
			'desc' 		=> __('Default = 12','jigoshop'),
			'tip' 		=> __('Determines how many products to display on Shop, Category and Tag pages before needing next and previous page navigation.','jigoshop'),
			'id' 		=> 'jigoshop_catalog_per_page',
			'std' 		=> '12',
			'type' 		=> 'text',
		);
		
		self::$default_options[] = array( 'name' => __('Pricing Options', 'jigoshop'), 'type' => 'title', 'desc' => '' );
		
		self::$default_options[] = array(
			'name'		=> __('Currency', 'jigoshop'),
			'desc' 		=> sprintf( __("This controls what currency prices are listed at in the catalog, and which currency PayPal, and other gateways, will take payments in. See the list of supported <a target='_new' href='%s'>PayPal currencies</a>.", 'jigoshop'), 'https://www.paypal.com/cgi-bin/webscr?cmd=p/sell/mc/mc_intro-outside' ),
			'tip' 		=> '',
			'id' 		=> 'jigoshop_currency',
			'std' 		=> 'GBP',
			'type' 		=> 'select',
			'choices'	=> apply_filters('jigoshop_currencies', array(
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
		);
		
		$cSymbol = get_jigoshop_currency_symbol();
		$cCode = self::get_option( 'jigoshop_currency' ) ? self::get_option( 'jigoshop_currency' ) : 'GBP';
		$cSep = self::get_option( 'jigoshop_price_decimal_sep' ) ? self::get_option( 'jigoshop_price_decimal_sep' ) : '.';
		
		self::$default_options[] = array(
			'name'		=> __('Currency display', 'jigoshop'),
			'desc' 		=> __("This controls the display of the currency symbol and currency code.", 'jigoshop'),
			'tip' 		=> '',
			'id' 		=> 'jigoshop_currency_pos',
			'std' 		=> 'left',
			'type' 		=> 'select',
			'choices'	=> array(
				'left'				=> __($cSymbol . '0' . $cSep . '00', 'jigoshop'),
				'left_space'		=> __($cSymbol . ' 0' . $cSep . '00', 'jigoshop'),
				'right'				=> __('0' . $cSep . '00' . $cSymbol, 'jigoshop'),
				'right_space'		=> __('0' . $cSep . '00 ' . $cSymbol, 'jigoshop'),
				'left_code'			=> __($cCode . '0' . $cSep . '00', 'jigoshop'),
				'left_code_space'	=> __($cCode . ' 0' . $cSep . '00', 'jigoshop'),
				'right_code'		=> __('0' . $cSep . '00' . $cCode, 'jigoshop'),
				'right_code_space'	=> __('0' . $cSep . '00 ' . $cCode, 'jigoshop'),
				'symbol_code'		=> __($cSymbol . '0' . $cSep . '00' . $cCode, 'jigoshop'),
				'symbol_code_space'	=> __($cSymbol . ' 0' . $cSep . '00 ' . $cCode, 'jigoshop'),
				'code_symbol'		=> __($cCode . '0' . $cSep . '00' . $cSymbol, 'jigoshop'),
				'code_symbol_space'	=> __($cCode . ' 0' . $cSep . '00 ' . $cSymbol, 'jigoshop'),
			)
		);
		
		self::$default_options[] = array(
			'name'		=> __('Thousand separator', 'jigoshop'),
			'desc' 		=> __('This sets the thousand separator of displayed prices.', 'jigoshop'),
			'tip' 		=> '',
			'id' 		=> 'jigoshop_price_thousand_sep',
			'std' 		=> ',',
			'type' 		=> 'text',
		);
		
		self::$default_options[] = array(
			'name'		=> __('Decimal separator', 'jigoshop'),
			'desc' 		=> __('This sets the decimal separator of displayed prices.', 'jigoshop'),
			'tip' 		=> '',
			'id' 		=> 'jigoshop_price_decimal_sep',
			'std' 		=> '.',
			'type' 		=> 'text',
		);
		
		self::$default_options[] = array(
			'name'		=> __('Number of decimals', 'jigoshop'),
			'desc' 		=> __('This sets the number of decimal points shown in displayed prices.', 'jigoshop'),
			'tip' 		=> '',
			'id' 		=> 'jigoshop_price_num_decimals',
			'std' 		=> '2',
			'type' 		=> 'text',
		);
		
		/**
		 * Images Tab
		 *------------------------------------------------------------------------------------------
		*/
		self::$default_options[] = array( 'type' => 'heading', 'name' => __('Images', 'jigoshop') );
		
		self::$default_options[] = array( 'name' => __('Image Options', 'jigoshop'), 'type' => 'title', 'desc' => __('<p>Large variations from the defaults could require CSS modifications in your Theme.</p>','jigoshop') );
		
		self::$default_options[] = array(
			'name' 		=> __('Tiny Image Width','jigoshop'),
			'desc' 		=> __('Default = 36px','jigoshop'),
			'tip' 		=> __('Set the width of the small image used in the Cart, Checkout, Orders and Widgets.','jigoshop'),
			'id' 		=> 'jigoshop_shop_tiny_w',
			'type' 		=> 'text',
			'std' 		=> 36
		);
		
		self::$default_options[] = array(
			'name' 		=> __('Tiny Image Height','jigoshop'),
			'desc' 		=> __('Default = 36px','jigoshop'),
			'tip' 		=> __('Set the height of the small image used in the Cart, Checkout, Orders and Widgets.','jigoshop'),
			'id' 		=> 'jigoshop_shop_tiny_h',
			'type' 		=> 'text',
			'std' 		=> 36
		);
		
		self::$default_options[] = array(
			'name' 		=> __('Thumbnail Image Width','jigoshop'),
			'desc' 		=> __('Default = 90px','jigoshop'),
			'tip' 		=> __('Set the width of the thumbnail image for Single Product page extra images.','jigoshop'),
			'id' 		=> 'jigoshop_shop_thumbnail_w',
			'type' 		=> 'text',
			'std' 		=> 90
		);
		
		self::$default_options[] = array(
			'name' 		=> __('Thumbnail Image Height','jigoshop'),
			'desc' 		=> __('Default = 90px','jigoshop'),
			'tip' 		=> __('Set the height of the thumbnail image for Single Product page extra images.','jigoshop'),
			'id' 		=> 'jigoshop_shop_thumbnail_h',
			'type' 		=> 'text',
			'std' 		=> 90
		);
		
		self::$default_options[] = array(
			'name' 		=> __('Catalog Image Width','jigoshop'),
			'desc' 		=> __('Default = 150px','jigoshop'),
			'tip' 		=> __('Set the width of the catalog image for Shop, Categories, Tags, and Related Products.','jigoshop'),
			'id' 		=> 'jigoshop_shop_small_w',
			'type' 		=> 'text',
			'std' 		=> 150
		);
		
		self::$default_options[] = array(
			'name' 		=> __('Catalog Image Height','jigoshop'),
			'desc' 		=> __('Default = 150px','jigoshop'),
			'tip' 		=> __('Set the height of the catalog image for Shop, Categories, Tags, and Related Products.','jigoshop'),
			'id' 		=> 'jigoshop_shop_small_h',
			'type' 		=> 'text',
			'std' 		=> 150
		);
		
		self::$default_options[] = array(
			'name' 		=> __('Large Image Width','jigoshop'),
			'desc' 		=> __('Default = 300px','jigoshop'),
			'tip' 		=> __('Set the width of the Single Product page large or Featured image.','jigoshop'),
			'id' 		=> 'jigoshop_shop_large_w',
			'type' 		=> 'text',
			'std' 		=> 300
		);
		
		self::$default_options[] = array(
			'name' 		=> __('Large Image Height','jigoshop'),
			'desc' 		=> __('Default = 300px','jigoshop'),
			'tip' 		=> __('Set the height of the Single Product page large or Featured image.','jigoshop'),
			'id' 		=> 'jigoshop_shop_large_h',
			'type' 		=> 'text',
			'std' 		=> 300
		);
		
		/**
		 * Coupons Tab
		 *------------------------------------------------------------------------------------------
		*/
		self::$default_options[] = array( 'type' => 'heading', 'name' => __('Coupons', 'jigoshop') );
		
		self::$default_options[] = array( 'name' => __('Coupon Information', 'jigoshop'), 'type' => 'title', 'desc' => __('<p>Coupons allow you to give your customers special offers and discounts. Leave product ID&#39;s blank to apply to all products in the cart. Separate each product ID with a comma.</p><p>Use either flat rates or percentage discounts for both cart totals and individual products. (do not enter a % sign, just a number). Product percentage discounts <strong>must</strong> have a product ID to be applied, otherwise use Cart Percentage Discount for all products.</p><p>"<em>Alone</em>" means <strong>only</strong> that coupon will be allowed for the whole cart.  If you have several of these, the last one entered by the customer will be used.</p>','jigoshop') );
		
		self::$default_options[] = array(
			'name'		=> __('Coupons','jigoshop'),
			'desc' 		=> '',
			'id' 		=> 'jigoshop_coupons',
			'type' 		=> 'coupons',
			'std' 		=> ''
		);
		
		/**
		 * Products & Inventory Tab
		 *------------------------------------------------------------------------------------------
		*/
		self::$default_options[] = array( 'type' => 'heading', 'name' => __('Products &amp; Inventory', 'jigoshop') );
		
		self::$default_options[] = array( 'name' => __('Product Options', 'jigoshop'), 'type' => 'title', 'desc' => '' );
		
		self::$default_options[] = array(
			'name'		=> __('Enable SKU field','jigoshop'),
			'desc' 		=> '',
			'tip' 		=> __('Turning off the SKU field will give products an SKU of their post id.','jigoshop'),
			'id' 		=> 'jigoshop_enable_sku',
			'std' 		=> 'no',
			'type' 		=> 'radio',
			'choices'	=> array(
				'no'			=> __('No', 'jigoshop'),
				'yes'			=> __('Yes', 'jigoshop')
			)
		);
		
		self::$default_options[] = array(
			'name'		=> __('Enable weight field','jigoshop'),
			'desc' 		=> '',
			'tip' 		=> '',
			'id' 		=> 'jigoshop_enable_weight',
			'std' 		=> 'yes',
			'type' 		=> 'radio',
			'choices'	=> array(
				'no'			=> __('No', 'jigoshop'),
				'yes'			=> __('Yes', 'jigoshop')
			)
		);
		
		self::$default_options[] = array(
			'name'		=> __('Weight Unit', 'jigoshop'),
			'desc' 		=> __("This controls what unit you will define weights in.", 'jigoshop'),
			'tip' 		=> '',
			'id' 		=> 'jigoshop_weight_unit',
			'std' 		=> 'kg',
			'type' 		=> 'select',
			'choices'	=> array(
				'kg'			=> __('Kilograms', 'jigoshop'),
				'lbs'			=> __('Pounds', 'jigoshop')
			)
		);
		
		self::$default_options[] = array(
			'name'		=> __('Enable product dimensions','jigoshop'),
			'desc' 		=> '',
			'tip' 		=> '',
			'id' 		=> 'jigoshop_enable_dimensions',
			'std' 		=> 'yes',
			'type' 		=> 'radio',
			'choices'	=> array(
				'no'			=> __('No', 'jigoshop'),
				'yes'			=> __('Yes', 'jigoshop')
			)
		);
		
		self::$default_options[] = array(
			'name'		=> __('Dimensions Unit', 'jigoshop'),
			'desc' 		=> __("This controls what unit you will define dimensions in.", 'jigoshop'),
			'tip' 		=> '',
			'id' 		=> 'jigoshop_dimension_unit',
			'std' 		=> 'cm',
			'type' 		=> 'select',
			'choices'	=> array(
				'cm'			=> __('centimeters', 'jigoshop'),
				'in'			=> __('inches', 'jigoshop')
			)
		);
		
		self::$default_options[] = array(
			'name'		=> __('Show related products','jigoshop'),
			'desc' 		=> '',
			'tip' 		=> __('To show or hide the related products section on a single product page.','jigoshop'),
			'id' 		=> 'jigoshop_enable_related_products',
			'std' 		=> 'yes',
			'type' 		=> 'radio',
			'choices'	=> array(
				'no'			=> __('No', 'jigoshop'),
				'yes'			=> __('Yes', 'jigoshop')
			)
		);
		
		self::$default_options[] = array( 'name' => __('Inventory Options', 'jigoshop'), 'type' => 'title', 'desc' => '' );
		
		self::$default_options[] = array(
			'name'		=> __('Manage stock','jigoshop'),
			'desc' 		=> __('If you are not managing stock, turn it off here to disable it in admin and on the front-end.','jigoshop'),
			'tip' 		=> __('You can manage stock on a per-item basis if you leave this option on.', 'jigoshop'),
			'id' 		=> 'jigoshop_manage_stock',
			'std' 		=> 'yes',
			'type' 		=> 'radio',
			'choices'	=> array(
				'no'			=> __('No', 'jigoshop'),
				'yes'			=> __('Yes', 'jigoshop')
			)
		);
		
		self::$default_options[] = array(
			'name'		=> __('Show stock amounts','jigoshop'),
			'desc' 		=> '',
			'tip' 		=> __('Set to yes to allow customers to view the amount of stock available for a product.', 'jigoshop'),
			'id' 		=> 'jigoshop_show_stock',
			'std' 		=> 'yes',
			'type' 		=> 'radio',
			'choices'	=> array(
				'no'			=> __('No', 'jigoshop'),
				'yes'			=> __('Yes', 'jigoshop')
			)
		);
		
		self::$default_options[] = array(
			'name'		=> __('Notify on low stock','jigoshop'),
			'desc' 		=> '',
			'id' 		=> 'jigoshop_notify_low_stock',
			'std' 		=> 'yes',
			'type' 		=> 'radio',
			'choices'	=> array(
				'no'			=> __('No', 'jigoshop'),
				'yes'			=> __('Yes', 'jigoshop')
			)
		);
		
		self::$default_options[] = array(
			'name'		=> __('Low stock threshold','jigoshop'),
			'desc' 		=> '',
			'tip' 		=> __('You will receive a notification as soon this threshold is hit (if notifications are turned on).', 'jigoshop'),
			'id' 		=> 'jigoshop_notify_low_stock_amount',
			'type' 		=> 'text',
			'std' 		=> '2'
		);
		
		self::$default_options[] = array(
			'name'		=> __('Notify on out of stock','jigoshop'),
			'desc' 		=> '',
			'id' 		=> 'jigoshop_notify_no_stock',
			'std' 		=> 'yes',
			'type' 		=> 'radio',
			'choices'	=> array(
				'no'			=> __('No', 'jigoshop'),
				'yes'			=> __('Yes', 'jigoshop')
			)
		);
		
		self::$default_options[] = array(
			'name'		=> __('Out of stock threshold','jigoshop'),
			'desc' 		=> '',
			'tip' 		=> __('You will receive a notification as soon this threshold is hit (if notifications are turned on).', 'jigoshop'),
			'id' 		=> 'jigoshop_notify_no_stock_amount',
			'type' 		=> 'text',
			'std' 		=> '0'
		);
		
		self::$default_options[] = array(
			'name'		=> __('Hide out of stock products','jigoshop'),
			'desc' 		=> '',
			'tip' 		=> 'For Yes: When the Out of Stock Threshold (above) is reached, the product visibility will be set to hidden so that it will not appear on the Catalog or Shop product lists.',
			'id' 		=> 'jigoshop_hide_no_stock_product',
			'std' 		=> 'no',
			'type' 		=> 'radio',
			'choices'	=> array(
				'no'			=> __('No', 'jigoshop'),
				'yes'			=> __('Yes', 'jigoshop')
			)
		);
		
		/**
		 * Tax Tab
		 *------------------------------------------------------------------------------------------
		*/
		self::$default_options[] = array( 'type' => 'heading', 'name' => __('Tax', 'jigoshop') );
		
		self::$default_options[] = array( 'name' => __('Tax Options', 'jigoshop'), 'type' => 'title', 'desc' => '' );
		
		self::$default_options[] = array(
			'name'		=> __('Calculate Taxes','jigoshop'),
			'desc' 		=> __('Only set this to no if you are exclusively selling non-taxable items.','jigoshop'),
			'tip' 		=> __('If you are not calculating taxes then you can ignore all other tax options.', 'jigoshop'),
			'id' 		=> 'jigoshop_calc_taxes',
			'std' 		=> 'yes',
			'type' 		=> 'radio',
			'choices'	=> array(
				'no'			=> __('No', 'jigoshop'),
				'yes'			=> __('Yes', 'jigoshop')
			)
		);
		
		self::$default_options[] = array(
			'name'		=> __('Catalog Prices include tax?','jigoshop'),
			'desc' 		=> '',
			'tip' 		=> __('If prices include tax then tax calculations will work backwards.','jigoshop'),
			'id' 		=> 'jigoshop_prices_include_tax',
			'std' 		=> 'yes',
			'type' 		=> 'radio',
			'choices'	=> array(
				'no'			=> __('No', 'jigoshop'),
				'yes'			=> __('Yes', 'jigoshop')
			)
		);
		
		self::$default_options[] = array(
			'name'		=> __('Cart totals display...','jigoshop'),
			'desc' 		=> '',
			'tip' 		=> __('Should the subtotal be shown including or excluding tax on the frontend?','jigoshop'),
			'id' 		=> 'jigoshop_display_totals_tax',
			'std' 		=> 'excluding',
			'type' 		=> 'select',
			'choices'	=> array(
				'including'		=> __('price including tax', 'jigoshop'),
				'excluding'		=> __('price excluding tax', 'jigoshop')
			)
		);
		
		self::$default_options[] = array(
			'name'		=> __('Additional Tax classes','jigoshop'),
			'desc' 		=> __('List 1 per line. This is in addition to the default <em>Standard Rate</em>.','jigoshop'),
			'tip' 		=> __('List product and shipping tax classes here, e.g. Zero Tax, Reduced Rate.','jigoshop'),
			'id' 		=> 'jigoshop_tax_classes',
			'type' 		=> 'textarea',
			'std' 		=> "Reduced Rate\nZero Rate"
		);
		
		self::$default_options[] = array(
			'name'		=> __('Tax rates','jigoshop'),
			'desc' 		=> '',
			'tip' 		=> __('To avoid rounding errors, insert tax rates with 4 decimal places.','jigoshop'),
			'id' 		=> 'jigoshop_tax_rates',
			'type' 		=> 'tax_rates',
			'std' 		=> ''
		);
		
		/**
		 * Shipping Tab
		 *------------------------------------------------------------------------------------------
		*/
		self::$default_options[] = array( 'type' => 'heading', 'name' => __('Shipping', 'jigoshop') );
		
		self::$default_options[] = array( 'name' => __('Shipping Options', 'jigoshop'), 'type' => 'title', 'desc' => '' );
		
		self::$default_options[] = array(
			'name'		=> __('Calculate Shipping','jigoshop'),
			'desc' 		=> __('Only set this to no if you are not shipping items, or items have shipping costs included.','jigoshop'),
			'tip' 		=> __('If you are not calculating shipping then you can ignore all other tax options.', 'jigoshop'),
			'id' 		=> 'jigoshop_calc_shipping',
			'std' 		=> 'yes',
			'type' 		=> 'radio',
			'choices'	=> array(
				'no'			=> __('No', 'jigoshop'),
				'yes'			=> __('Yes', 'jigoshop')
			)
		);
		
		self::$default_options[] = array(
			'name'		=> __('Enable shipping calculator on cart','jigoshop'),
			'desc' 		=> '',
			'tip' 		=> '',
			'id' 		=> 'jigoshop_enable_shipping_calc',
			'std' 		=> 'yes',
			'type' 		=> 'radio',
			'choices'	=> array(
				'no'			=> __('No', 'jigoshop'),
				'yes'			=> __('Yes', 'jigoshop')
			)
		);
		
		self::$default_options[] = array(
			'name'		=> __('Only ship to billing address?','jigoshop'),
			'desc' 		=> '',
			'tip' 		=> '',
			'id' 		=> 'jigoshop_ship_to_billing_address_only',
			'std' 		=> 'no',
			'type' 		=> 'radio',
			'choices'	=> array(
				'no'			=> __('No', 'jigoshop'),
				'yes'			=> __('Yes', 'jigoshop')
			)
		);
		
//		self::$default_options[] = array( 'type' => 'shipping_options');  // (-JAP-) should not longer be required
		
		/**
		 * Payment Gateways Tab
		 *------------------------------------------------------------------------------------------
		*/
		self::$default_options[] = array( 'type' => 'heading', 'name' => __('Payment Gateways', 'jigoshop') );
		
		self::$default_options[] = array( 'name' => __('Available gateways', 'jigoshop'), 'type' => 'title', 'desc' => '' );
		
//		self::$default_options[] = array( 'type' => 'gateway_options');  // (-JAP-) should not longer be required
				
	}
	
}


?>
