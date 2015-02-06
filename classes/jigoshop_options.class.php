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
 * @package     Jigoshop
 * @category    Core
 * @author      Jigoshop
 * @copyright   Copyright © 2011-2014 Jigoshop.
 * @license     GNU General Public License v3
 */

/**
 *  ====================
 *
 * Supported Option Types:
 *      text                    - standard text input (display size 20 chars)
 *      midtext                 - same as text (display size 40 chars)
 *      longtext                - same as text (display size 80 chars)
 *      email                   - same as text (display size 40 chars)
 *      textarea                - same as text (display size 4 rows, 60 cols)
 *      codeblock               - intended for markup and embedded javascript for inclusion elsewhere
 *      natural                 - positive number only, leading 0 allowed (display size 20 chars)
 *      integer                 - integer, positive or negative, no decimals (display size 20 chars)
 *      decimal                 - positive or negative number, may contain decimal point (display size 20 chars)
 *      checkbox                - true or false option type
 *      multicheck              - option grouping allows multiple options for selection (horizontal or vertical display)
 *      select                  - standard select option with pre-defined choices
 *      radio                   - option grouping allowing one option for selection (horizontal or vertical display)
 *      range                   - range slider with min, max, and step values
 *      single_select_page      - select that lists all available WordPress pages with a 'None' choice as well
 *      single_select_country   - select allowing a single choice of all Jigoshop defined countries
 *      multi_select_countries  - multicheck allowing multiple choices of all Jigoshop defined countries
 *      user_defined            - a user installed option type, must provide display and option update callbacks
 *
 *  ====================
 *
 *  The Options array uses Tabs for display and each tab begins with a 'tab' option type
 *  Each Tab Heading may be optionally divided into sections defined by a 'title' option type
 *  A Payment Gateway for example, would install itself into a 'tab' and provide a section 'title' with options
 *  List each option sequentially for display under each 'title' or 'tab' option type
 *
 *  Each Option may have any or all of the following items: (for an option, 'id' is MANDATORY and should be unique)
		'tab'           => '',                      - calculated based on position in array
		'section'       => '',                      - calculated based on position in array
		'id'            => null,                    - required
		'type'          => '',                      - required
		'name'          => __( '', 'jigoshop' ),    - used for Option title in Admin display
		'desc'          => __( '', 'jigoshop' ),    - option descriptive information appears under the option in Admin
		'tip'           => __( '', 'jigoshop' ),    - a pop-up tool tip providing help information
		'std'           => '',                      - required, default value for the option
		'choices'       => array(),                 - for selects, radios, etc.
		'class'         => '',                      - any special CSS classes to assign to the options display
		'display'       => null,        - call back function for 'user_defined' - array( $this, 'function_name' )
		'update'        => null,        - call back function for 'user_defined' - array( $this, 'function_name' )
		'extra'         => null,                    - for display and verification - array( 'horizontal' )
 *
 *  ====================
 *
 * Example checkbox option definition:              // Choices should be defined with 'yes' and 'no'
		self::$default_options[] = array(
			'name'		=> __('Jigoshop Checkbox Testing','jigoshop'),
			'desc' 		=> '',
			'tip' 		=> '',
			'id' 		=> 'jigoshop_checkbox_test',
			'type' 		=> 'checkbox',
			'std' 		=> 'yes',
			'choices'	=> array(
				'no'			=> __('No', 'jigoshop'),
				'yes'			=> __('Yes', 'jigoshop')
			)
		);
 *
 *  ====================
 *
 * Example range option definition:
		self::$default_options[] = array(
			'name'		=> __('Jigoshop Range Testing','jigoshop'),
			'desc' 		=> '',
			'tip' 		=> '',
			'id' 		=> 'jigoshop_range_test',
			'type' 		=> 'range',
			'std' 		=> 100,
			'extra'		=> array(
				'min'			=> 50,
				'max'			=> 300,
				'step'			=> 5
			)
		);
 *
 *  ====================
 *
 * Example vertical multicheck option definition:
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
			'extra'		=> array( 'vertical' )
		);
 *
 */

class Jigoshop_Options implements Jigoshop_Options_Interface {
	private static $default_options;
	private static $current_options;
	private $bad_extensions = array();

	/**
	 * Instantiates a new Options object
	 *
	 * @return Jigoshop_Options
	 * @since  1.3
	 */
	public function __construct(){
		self::$current_options = array();

		$options = get_option(JIGOSHOP_OPTIONS);
		if(is_array($options)){
			self::$current_options = $options;
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
	 * @since	1.3
	 */
	public function update_options(){
		update_option(JIGOSHOP_OPTIONS, self::$current_options);
	}

	/**
	 * Adds a named option to our collection
	 *
	 * Will do nothing if option already exists to match WordPress behaviour
	 * Use 'set' to actually set an existing option
	 *
	 * @param string $name the name of the option to add
	 * @param mixed	$value the value to set if the option doesn't exist
	 * @since	1.3
	 */
	public function add_option($name, $value){
		$this->add($name, $value);
	}

	/**
	 * Adds a named option
	 * Will do nothing if option already exists to match WordPress behaviour
	 * Use 'set' to actually set an existing option
	 *
	 * @param   string  the name of the option to add
	 * @param   mixed  the value to set if the option doesn't exist
	 * @since  1.12
	 */
	public function add($name, $value)
	{
		$this->get_current_options();
		if(!isset(self::$current_options[$name])){
			self::$current_options[$name] = $value;
			if(!has_action('shutdown', array($this, 'update_options'))){
				add_action('shutdown', array($this, 'update_options'));
			}
		}
	}

	/**
	 * Return the Jigoshop current options
	 *
	 * @return array the entire current options array is returned
	 * @since	1.3
	 */
	public function get_current_options(){
		if(empty(self::$current_options)){
			if(empty(self::$default_options)){
				$this->set_default_options();
			}
			$this->set_current_options(self::$default_options);
		}

		return self::$current_options;
	}

	/**
	 * Sets the Jigoshop default options
	 *
	 * This will create the default options array. Extensions may install options of the same format into this.
	 *
	 * @param   none
	 * @return  Void
	 *
	 * @since	1.3
	 *
	 */
	private function set_default_options(){
		$symbols = jigoshop::currency_symbols();
		$countries = jigoshop::currency_countries();

		$currencies = array();
		foreach($countries as $key => $country){
			$currencies[$key] = $country.' ('.$symbols[$key].')';
		}
		$currencies = apply_filters('jigoshop_currencies', $currencies);

		$cSymbol = '';
		if(function_exists('get_jigoshop_currency_symbol')){
			$cSymbol = get_jigoshop_currency_symbol();
		}

		$cCode = $this->get('jigoshop_currency') ? $this->get('jigoshop_currency') : 'GBP';
		$cSep = $this->get('jigoshop_price_decimal_sep') ? $this->get('jigoshop_price_decimal_sep') : '.';

		self::$default_options = array(
			// Shop tab
			array('type' => 'tab', 'name' => __('Shop', 'jigoshop')),
			array('name' => __('Shop Options', 'jigoshop'), 'type' => 'title', 'desc' => ''),
			array(
				'name' => __('Base Country/Region', 'jigoshop'),
				'desc' => '',
				'tip' => __('This is the base country for your business. Tax rates will be based on this country.', 'jigoshop'),
				'id' => 'jigoshop_default_country',
				'type' => 'single_select_country',
			),
			array(
				'name' => __('Default Country/Region for customer', 'jigoshop'),
				'desc' => '',
				'tip' => __('This is the country for your clients with new accounts.', 'jigoshop'),
				'id' => 'jigoshop_default_country_for_customer',
				'std' => $this->get('jigoshop_default_country'),
				'type' => 'single_select_country',
				'options' => array(
					'add_empty' => true,
				),
			),
			array(
				'name' => __('Currency', 'jigoshop'),
				'desc' => '',
				'tip' => __('This controls what currency the prices are listed with in the Catalog, and which currency PayPal, and other gateways, will take payments in.', 'jigoshop'),
				'id' => 'jigoshop_currency',
				'type' => 'select',
				'choices' => $currencies,
			),
			array(
				'name' => __('Allowed Countries', 'jigoshop'),
				'desc' => '',
				'tip' => __('These are countries that you are willing to ship to.', 'jigoshop'),
				'id' => 'jigoshop_allowed_countries',
				'type' => 'select',
				'choices' => array(
					'all' => __('All Countries', 'jigoshop'),
					'specific' => __('Specific Countries', 'jigoshop'),
				),
			),
			array(
				'name' => __('Specific Countries', 'jigoshop'),
				'desc' => '',
				'tip' => '',
				'id' => 'jigoshop_specific_allowed_countries',
				'type' => 'multi_select_countries',
			),
			array(
				'name' => __('Demo store', 'jigoshop'),
				'desc' => '',
				'tip' => __('Enable this option to show a banner at the top of every page stating this shop is currently in testing mode.', 'jigoshop'),
				'id' => 'jigoshop_demo_store',
				'type' => 'checkbox',
				'choices' => array(
					'no' => __('No', 'jigoshop'),
					'yes' => __('Yes', 'jigoshop'),
				),
			),
			array('name' => __('Invoicing', 'jigoshop'), 'type' => 'title', 'desc' => ''),
			array(
				'name' => __('Company Name', 'jigoshop'),
				'desc' => '',
				'tip' => __('Setting your company name will enable us to print it out on your invoice emails. Leave blank to disable.', 'jigoshop'),
				'id' => 'jigoshop_company_name',
				'type' => 'text',
			),
			array(
				'name' => __('Tax Registration Number', 'jigoshop'),
				'desc' => __('Add your tax registration label before the registration number and it will be printed as well. eg. <code>VAT Number: 88888888</code>', 'jigoshop'),
				'tip' => __('Setting your tax number will enable us to print it out on your invoice emails. Leave blank to disable.', 'jigoshop'),
				'id' => 'jigoshop_tax_number',
				'type' => 'text',
			),
			array(
				'name' => __('Address Line1', 'jigoshop'),
				'desc' => '',
				'tip' => __('Setting your address will enable us to print it out on your invoice emails. Leave blank to disable.', 'jigoshop'),
				'id' => 'jigoshop_address_1',
				'type' => 'longtext',
			),
			array(
				'name' => __('Address Line2', 'jigoshop'),
				'desc' => '',
				'tip' => __('If address line1 is not set, address line2 will not display even if you put a value in it. Setting your address will enable us to print it out on your invoice emails. Leave blank to disable.', 'jigoshop'),
				'id' => 'jigoshop_address_2',
				'type' => 'longtext',
			),
			array(
				'name' => __('Company Phone', 'jigoshop'),
				'desc' => '',
				'tip' => __('Setting your company phone number will enable us to print it out on your invoice emails. Leave blank to disable.', 'jigoshop'),
				'id' => 'jigoshop_company_phone',
				'type' => 'text',
			),
			array(
				'name' => __('Company Email', 'jigoshop'),
				'desc' => '',
				'tip' => __('Setting your company email will enable us to print it out on your invoice emails. Leave blank to disable.', 'jigoshop'),
				'id' => 'jigoshop_company_email',
				'type' => 'email',
			),
			array('name' => __('Permalinks', 'jigoshop'), 'type' => 'title', 'desc' => ''),
			array(
				'name' => __('Prepend shop categories and tags with base page', 'jigoshop'),
				'desc' => '',
				'tip' => __('This will only apply to tags &amp; categories.<br/>Enabled: http://yoursite.com / product_category / YourCategory<br/>Disabled: http://yoursite.com / base_page / product_category / YourCategory', 'jigoshop'),
				'id' => 'jigoshop_prepend_shop_page_to_urls',
				'type' => 'checkbox',
				'choices' => array(
					'no' => __('No', 'jigoshop'),
					'yes' => __('Yes', 'jigoshop'),
				),
			),
			array(
				'name' => __('Prepend product permalinks with shop base page', 'jigoshop'),
				'desc' => '',
				'tip' => '',
				'id' => 'jigoshop_prepend_shop_page_to_product',
				'type' => 'checkbox',
				'choices' => array(
					'no' => __('No', 'jigoshop'),
					'yes' => __('Yes', 'jigoshop'),
				),
			),
			array(
				'name' => __('Prepend product permalinks with product category', 'jigoshop'),
				'desc' => '',
				'tip' => '',
				'id' => 'jigoshop_prepend_category_to_product',
				'type' => 'checkbox',
				'choices' => array(
					'no' => __('No', 'jigoshop'),
					'yes' => __('Yes', 'jigoshop'),
				),
			),
			array(
				'name' => __('Product category slug', 'jigoshop'),
				'desc' => '',
				'tip' => __('Slug displayed in product category URLs. Leave blank to use default "product-category"', 'jigoshop'),
				'id' => 'jigoshop_product_category_slug',
				'type' => 'text',
			),
			array(
				'name' => __('Product tag slug', 'jigoshop'),
				'desc' => '',
				'tip' => __('Slug displayed in product tag URLs. Leave blank to use default "product-tag"', 'jigoshop'),
				'id' => 'jigoshop_product_tag_slug',
				'type' => 'text',
			),
			// General tab
			array('type' => 'tab', 'name' => __('General', 'jigoshop')),
			array('name' => __('General Options', 'jigoshop'), 'type' => 'title', 'desc' => ''),
			array(
				'name' => __('Cart shows "Return to Shop" button', 'jigoshop'),
				'desc' => '',
				'tip' => __('Enabling this setting will display a "Return to Shop" button on the Cart page along with the "Continue to Checkout" button.', 'jigoshop'),
				'id' => 'jigoshop_cart_shows_shop_button',
				'type' => 'checkbox',
				'choices' => array(
					'no' => __('No', 'jigoshop'),
					'yes' => __('Yes', 'jigoshop'),
				),
			),
			array(
				'name' => __('After adding product to cart', 'jigoshop'),
				'desc' => '',
				'tip' => __('Define what should happen when a user clicks on &#34;Add to Cart&#34; on any product or page.', 'jigoshop'),
				'id' => 'jigoshop_redirect_add_to_cart',
				'type' => 'radio',
				'extra' => array('vertical'),
				'choices' => array(
					'same_page' => __('Stay on the same page', 'jigoshop'),
					'to_checkout' => __('Redirect to Checkout', 'jigoshop'),
					'to_cart' => __('Redirect to Cart', 'jigoshop'),
				),
			),
			array(
				'name' => __('Cart status after login', 'jigoshop'),
				'desc' => __('Current cart <b>always</b> will be loaded if customer logs in checkout page.', 'jigoshop'),
				'tip' => __("Define what should happen with shopping cart if customer added items to shopping cart as guest and than he logs in to your shop.", 'jigoshop'),
				'id' => 'jigoshop_cart_after_login',
				'type' => 'select',
				'choices' => array(
					'load_saved' => __('Load saved cart', 'jigoshop'),
					'load_current' => __('Load current cart', 'jigoshop'),
					'merge' => __('Merge saved and current carts', 'jigoshop'),
				)
			),
			array(
				'name' => __('Reset pending Orders', 'jigoshop'),
				'desc' => __("Change all 'Pending' Orders older than one month to 'On Hold'", 'jigoshop'),
				'tip' => __("For customers that have not completed the Checkout process or haven't paid for an Order after a period of time, this will reset the Order to On Hold allowing the Shop owner to take action.  WARNING: For the first use on an existing Shop this setting <em>can</em> generate a <strong>lot</strong> of email!", 'jigoshop'),
				'id' => 'jigoshop_reset_pending_orders',
				'type' => 'checkbox',
				'choices' => array(
					'no' => __('No', 'jigoshop'),
					'yes' => __('Yes', 'jigoshop'),
				),
			),
			array(
				'name' => __('Complete processing Orders', 'jigoshop'),
				'desc' => __("Change all 'Processing' Orders older than one month to 'Completed'", 'jigoshop'),
				'tip' => __("For orders that have been completed but the status is still set to 'processing'.  This will move them to a 'completed' status without sending an email out to all the customers.", 'jigoshop'),
				'id' => 'jigoshop_complete_processing_orders',
				'type' => 'checkbox',
				'choices' => array(
					'no' => __('No', 'jigoshop'),
					'yes' => __('Yes', 'jigoshop'),
				),
			),
			array(
				'name' => __('Enforce login for downloads', 'jigoshop'),
				'desc' => '',
				'tip' => __('If a guest purchases a download, the guest can still download a link without logging in. We recommend disabling guest purchases if you enable this option.', 'jigoshop'),
				'id' => 'jigoshop_downloads_require_login',
				'type' => 'checkbox',
				'choices' => array(
					'no' => __('No', 'jigoshop'),
					'yes' => __('Yes', 'jigoshop'),
				),
			),
			array(
				'name' => __('Disable Jigoshop frontend.css', 'jigoshop'),
				'desc' => __('(The next option below will have no effect if this one is disabled)', 'jigoshop'),
				'tip' => __('Useful if you want to disable Jigoshop styles and theme it yourself via your theme.', 'jigoshop'),
				'id' => 'jigoshop_disable_css',
				'type' => 'checkbox',
				'choices' => array(
					'no' => __('No', 'jigoshop'),
					'yes' => __('Yes', 'jigoshop'),
				),
			),
			array(
				'name' => __('Include extra theme styles with Jigoshop frontend.css', 'jigoshop'),
				'desc' => '',
				'tip' => __("With this option <em>on</em>, Jigoshop's default frontend.css will still load, and any extra bits found in 'theme/jigoshop/style.css' for over-rides will also be loaded.", 'jigoshop'),
				'id' => 'jigoshop_frontend_with_theme_css',
				'type' => 'checkbox',
				'choices' => array(
					'no' => __('No', 'jigoshop'),
					'yes' => __('Yes', 'jigoshop'),
				),
			),
			array(
				'name' => __('Disable bundled Lightbox', 'jigoshop'),
				'desc' => __('Product galleries and images as well as the Add Review form will open in a lightbox.', 'jigoshop'),
				'tip' => __('Useful if your theme or other plugin already loads our Lightbox script and css (prettyPhoto), or you want to use a different one.', 'jigoshop'),
				'id' => 'jigoshop_disable_fancybox',
				'type' => 'checkbox',
				'choices' => array(
					'no' => __('No', 'jigoshop'),
					'yes' => __('Yes', 'jigoshop'),
				),
			),
			array(
			'name' => __('Use custom product category order', 'jigoshop'),
			'desc' => '',
			'tip' => __('This option allows to make custom product category order, by drag and drop method.', 'jigoshop'),
			'id' => 'jigoshop_enable_draggable_categories',
			'type' => 'checkbox',
			'choices' => array(
				'no' => __('No', 'jigoshop'),
				'yes' => __('Yes', 'jigoshop'),
			),
		),
			array('name' => __('Jigoshop messages', 'jigoshop'), 'type' => 'title', 'desc' => ''),
			array(
				'name' => __('Message disappear time', 'jigoshop'),
				'desc' => __('How long message is displayed before disappearing (in ms). Set to 0 to keep it displayed.', 'jigoshop'),
				'id' => 'jigoshop_message_disappear_time',
				'type' => 'natural',
			),
			array(
				'name' => __('Error disappear time', 'jigoshop'),
				'desc' => __('How long error is displayed before disappearing (in ms). Set to 0 to keep it displayed.', 'jigoshop'),
				'id' => 'jigoshop_error_disappear_time',
				'type' => 'natural',
			),
			array('name' => __('Email Details', 'jigoshop'), 'type' => 'title', 'desc' => ''),
			array(
				'name' => __('Jigoshop email address', 'jigoshop'),
				'desc' => '',
				'tip' => __('The email address used to send all Jigoshop related emails, such as order confirmations and notices.  This may be different than your Company email address on "Shop Tab -> Invoicing".', 'jigoshop'),
				'id' => 'jigoshop_email',
				'type' => 'email',
			),
			array(
				'name' => __('Email from name', 'jigoshop'),
				'desc' => '',
				'tip' => __('', 'jigoshop'),
				'id' => 'jigoshop_email_from_name',
				'type' => 'text',
			),
			array(
				'name' => __('Email footer', 'jigoshop'),
				'desc' => '',
				'tip' => __('The email footer used in all jigoshop emails.', 'jigoshop'),
				'id' => 'jigoshop_email_footer',
				'type' => 'textarea',
			),
			array(
				'name' => __('Generate default emails', 'jigoshop'),
				'desc' => '',
				'tip' => '',
				'id' => 'jigoshop_email_generete_defaults',
				'type' => 'user_defined',
				'display' => array($this, 'generate_defaults_emails'),
			),
			array('name' => __('Checkout page', 'jigoshop'), 'type' => 'title', 'desc' => ''),
			array(
				'name' => __('Validate postal/zip codes', 'jigoshop'),
				'desc' => '',
				'tip' => __('Enabling this setting will force proper postcodes to be entered by a customer for a country.', 'jigoshop'),
				'id' => 'jigoshop_enable_postcode_validating',
				'type' => 'checkbox',
				'choices' => array(
					'no' => __('No', 'jigoshop'),
					'yes' => __('Yes', 'jigoshop'),
				),
			),
			array(
				'name' => __('Show verify information message', 'jigoshop'),
				'desc' => '',
				'tip' => __('Enabling this setting will display a message at the bottom of the Checkout asking customers to verify all their informatioin is correctly entered before placing their Order.  This is useful in particular for Countries that have states to ensure the correct shipping state is selected.', 'jigoshop'),
				'id' => 'jigoshop_verify_checkout_info_message',
				'type' => 'checkbox',
				'choices' => array(
					'no' => __('No', 'jigoshop'),
					'yes' => __('Yes', 'jigoshop'),
				),
			),
			array(
				'name' => __('Show EU VAT reduction message', 'jigoshop'),
				'desc' => __('This will only apply to EU Union based Shops.', 'jigoshop'),
				'tip' => __('Enabling this setting will display a message at the bottom of the Checkout informing the customer that EU VAT will not be removed until the Order is placed and only if they have provided a valid EU VAT Number.', 'jigoshop'),
				'id' => 'jigoshop_eu_vat_reduction_message',
				'type' => 'checkbox',
				'choices' => array(
					'no' => __('No', 'jigoshop'),
					'yes' => __('Yes', 'jigoshop'),
				),
			),
			array(
				'name' => __('Allow guest purchases', 'jigoshop'),
				'desc' => '',
				'tip' => __('Enabling this setting will allow users to checkout without registering or signing up. Otherwise, users must be signed in or must sign up to checkout.', 'jigoshop'),
				'id' => 'jigoshop_enable_guest_checkout',
				'type' => 'checkbox',
				'choices' => array(
					'no' => __('No', 'jigoshop'),
					'yes' => __('Yes', 'jigoshop'),
				),
			),
			array(
				'name' => __('Show login form', 'jigoshop'),
				'desc' => '',
				'id' => 'jigoshop_enable_guest_login',
				'type' => 'checkbox',
				'choices' => array(
					'no' => __('No', 'jigoshop'),
					'yes' => __('Yes', 'jigoshop'),
				),
			),
			array(
				'name' => __('Allow registration', 'jigoshop'),
				'desc' => '',
				'id' => 'jigoshop_enable_signup_form',
				'type' => 'checkbox',
				'choices' => array(
					'no' => __('No', 'jigoshop'),
					'yes' => __('Yes', 'jigoshop'),
				),
			),
			array(
				'name' => __('Force SSL on checkout', 'jigoshop'),
				'desc' => '',
				'tip' => __('This will load your checkout page with https://. An SSL certificate is <strong>required</strong> if you choose yes. Contact your hosting provider for more information on SSL Certs.', 'jigoshop'),
				'id' => 'jigoshop_force_ssl_checkout',
				'type' => 'checkbox',
				'choices' => array(
					'no' => __('No', 'jigoshop'),
					'yes' => __('Yes', 'jigoshop'),
				),
			),
			array('name' => __('Integration', 'jigoshop'), 'type' => 'title', 'desc' => ''),
			array(
				'name' => __('ShareThis Publisher ID', 'jigoshop'),
				'desc' => __("Enter your <a href='http://sharethis.com/account/'>ShareThis publisher ID</a> to show ShareThis on product pages.", 'jigoshop'),
				'tip' => __('ShareThis is a small social sharing widget for posting links on popular sites such as Twitter and Facebook.', 'jigoshop'),
				'id' => 'jigoshop_sharethis',
				'type' => 'text',
			),
			array(
				'name' => __('Google Analytics ID', 'jigoshop'),
				'desc' => __('Log into your Google Analytics account to find your ID. e.g. <code>UA-XXXXXXX-X</code>', 'jigoshop'),
				'id' => 'jigoshop_ga_id',
				'type' => 'text',
			),
			array(
				'name' => __('Enable eCommerce Tracking', 'jigoshop'),
				'tip' => __('Add Google Analytics eCommerce tracking code upon successful orders', 'jigoshop'),
				'desc' => __('<a href="//support.google.com/analytics/bin/answer.py?hl=en&answer=1009612">Learn how to enable</a> eCommerce tracking for your Google Analytics account.', 'jigoshop'),
				'id' => 'jigoshop_ga_ecommerce_tracking_enabled',
				'type' => 'checkbox',
				'choices' => array(
					'no' => __('No', 'jigoshop'),
					'yes' => __('Yes', 'jigoshop'),
				),
			),
			// Pages tab
			array('type' => 'tab', 'name' => __('Pages', 'jigoshop')),
			array('name' => __('Page configurations', 'jigoshop'), 'type' => 'title', 'desc' => ''),
			array(
				'name' => __('Cart Page', 'jigoshop'),
				'desc' => __('Shortcode to place on page: <code>[jigoshop_cart]</code>', 'jigoshop'),
				'tip' => '',
				'id' => 'jigoshop_cart_page_id',
				'type' => 'single_select_page',
			),
			array(
				'name' => __('Checkout Page', 'jigoshop'),
				'desc' => __('Shortcode to place on page: <code>[jigoshop_checkout]</code>', 'jigoshop'),
				'tip' => '',
				'id' => 'jigoshop_checkout_page_id',
				'type' => 'single_select_page',
			),
			array(
				'name' => __('Pay Page', 'jigoshop'),
				'desc' => __('Shortcode to place on page: <code>[jigoshop_pay]</code><br/>Default parent page: Checkout', 'jigoshop'),
				'tip' => '',
				'id' => 'jigoshop_pay_page_id',
				'type' => 'single_select_page',
			),
			array(
				'name' => __('Thanks Page', 'jigoshop'),
				'desc' => __('Shortcode to place on page: <code>[jigoshop_thankyou]</code><br/>Default parent page: Checkout', 'jigoshop'),
				'tip' => '',
				'id' => 'jigoshop_thanks_page_id',
				'type' => 'single_select_page',
			),
			array(
				'name' => __('My Account Page', 'jigoshop'),
				'desc' => __('Shortcode to place on page: <code>[jigoshop_my_account]</code>', 'jigoshop'),
				'tip' => '',
				'id' => 'jigoshop_myaccount_page_id',
				'type' => 'single_select_page',
			),
			array(
				'name' => __('Edit Address Page', 'jigoshop'),
				'desc' => __('Shortcode to place on page: <code>[jigoshop_edit_address]</code><br/>Default parent page: My Account', 'jigoshop'),
				'tip' => '',
				'id' => 'jigoshop_edit_address_page_id',
				'type' => 'single_select_page',
			),
			array(
				'name' => __('View Order Page', 'jigoshop'),
				'desc' => __('Shortcode to place on page: <code>[jigoshop_view_order]</code><br/>Default parent page: My Account', 'jigoshop'),
				'tip' => '',
				'id' => 'jigoshop_view_order_page_id',
				'type' => 'single_select_page',
			),
			array(
				'name' => __('Change Password Page', 'jigoshop'),
				'desc' => __('Shortcode to place on page: <code>[jigoshop_change_password]</code><br/>Default parent page: My Account', 'jigoshop'),
				'tip' => '',
				'id' => 'jigoshop_change_password_page_id',
				'type' => 'single_select_page',
			),
			array(
				'name' => __('Track Order Page', 'jigoshop'),
				'desc' => __('Shortcode to place on page: <code>[jigoshop_order_tracking]</code>', 'jigoshop'),
				'tip' => '',
				'id' => 'jigoshop_track_order_page_id',
				'type' => 'single_select_page',
			),
			array(
				'name' => __('Terms Page', 'jigoshop'),
				'desc' => __('If you define a &#34;Terms&#34; page the customer will be asked to accept it before allowing them to place their order.', 'jigoshop'),
				'tip' => '',
				'id' => 'jigoshop_terms_page_id',
				'type' => 'single_select_page',
				'extra' => 'show_option_none='.__('None', 'jigoshop'),
			),
			// Catalog & Pricing tab
			array('type' => 'tab', 'name' => __('Catalog &amp; Pricing', 'jigoshop')),
			array('name' => __('Catalog Options', 'jigoshop'), 'type' => 'title', 'desc' => ''),
			array(
				'name' => __('Catalog base page', 'jigoshop'),
				'desc' => '',
				'tip' => __('This sets the base page of your shop. You should not change this value once you have launched your site otherwise you risk breaking urls of other sites pointing to yours, etc.', 'jigoshop'),
				'id' => 'jigoshop_shop_page_id',
				'type' => 'single_select_page',
			),
			array(
				'name' => __('Shop redirection page', 'jigoshop'),
				'desc' => '',
				'tip' => __('This will point users to the page you set for buttons like `Return to shop` or `Continue Shopping`.', 'jigoshop'),
				'id' => 'jigoshop_shop_redirect_page_id',
				'type' => 'single_select_page',
			),
			array(
				'name' => __('Catalog product buttons show', 'jigoshop'),
				'desc' => '',
				'tip' => __('This will determine the type of button and the action it will use when clicked on the Shop and Category product listings.  You can also set it to use no button.', 'jigoshop'),
				'id' => 'jigoshop_catalog_product_button',
				'type' => 'radio',
				'choices' => array(
					'add' => __('Add to Cart', 'jigoshop'),
					'view' => __('View Product', 'jigoshop'),
					'none' => __('No Button', 'jigoshop'),
				),
			),
			array(
				'name' => __('Sort products in catalog by', 'jigoshop'),
				'desc' => '',
				'tip' => __('Determines the display sort order of products for the Shop, Categories, and Tag pages.', 'jigoshop'),
				'id' => 'jigoshop_catalog_sort_orderby',
				'type' => 'radio',
				'choices' => array(
					'post_date' => __('Creation Date', 'jigoshop'),
					'title' => __('Product Title', 'jigoshop'),
					'menu_order' => __('Product Post Order', 'jigoshop'),
				),
			),
			array(
				'name' => __('Catalog sort direction', 'jigoshop'),
				'desc' => '',
				'tip' => __('Determines whether the catalog sort orderby is ascending or descending.', 'jigoshop'),
				'id' => 'jigoshop_catalog_sort_direction',
				'type' => 'radio',
				'choices' => array(
					'asc' => __('Ascending', 'jigoshop'),
					'desc' => __('Descending', 'jigoshop'),
				),
			),
			array(
				'name' => __('Catalog products per row', 'jigoshop'),
				'desc' => __('Default = 3', 'jigoshop'),
				'tip' => __('Determines how many products to show on one display row for Shop, Category and Tag pages.', 'jigoshop'),
				'id' => 'jigoshop_catalog_columns',
				'type' => 'number',
				'extra' => array(
					'min' => 1,
					'max' => 10,
					'step' => 1,
				),
			),
			array(
				'name' => __('Catalog products per page', 'jigoshop'),
				'desc' => __('Default = 12', 'jigoshop'),
				'tip' => __('Determines how many products to display on Shop, Category and Tag pages before needing next and previous page navigation.', 'jigoshop'),
				'id' => 'jigoshop_catalog_per_page',
				'type' => 'number',
				'extra' => array(
					'min' => 1,
					'max' => 100,
					'step' => 1,
				),
			),
			array('name' => __('Pricing Options', 'jigoshop'), 'type' => 'title', 'desc' => ''),
			array(
				'name' => __('Show prices with tax', 'jigoshop'),
				'desc' => __("This controls the display of the product price in cart and checkout page.", 'jigoshop'),
				'tip' => '',
				'id' => 'jigoshop_show_prices_with_tax',
				'type' => 'checkbox',
				'choices' => array(
					'no' => __('No', 'jigoshop'),
					'yes' => __('Yes', 'jigoshop'),
				),
			),
			array(
				'name' => __('Currency display', 'jigoshop'),
				'desc' => __("This controls the display of the currency symbol and currency code.", 'jigoshop'),
				'tip' => '',
				'id' => 'jigoshop_currency_pos',
				'type' => 'select',
				'choices' => array(
					'left' => sprintf('%1$s0%2$s00', $cSymbol, $cSep),// symbol.'0'.separator.'00'
					'left_space' => sprintf('%1$s0 %2$s00', $cSymbol, $cSep),// symbol.' 0'.separator.'00'
					'right' => sprintf('0%2$s00%1$s', $cSymbol, $cSep),// '0'.separator.'00'.symbol
					'right_space' => sprintf('0%2$s00 %1$s', $cSymbol, $cSep),// '0'.separator.'00 '.symbol
					'left_code' => sprintf('%1$s0%2$s00', $cCode, $cSep),// code.'0'.separator.'00'
					'left_code_space' => sprintf('%1$s 0%2$s00', $cCode, $cSep),// code.' 0'.separator.'00'
					'right_code' => sprintf('0%2$s00%1$s', $cCode, $cSep),// '0'.separator.'00'.code
					'right_code_space' => sprintf('0%2$s00 %1$s', $cCode, $cSep),// '0'.separator.'00 '.code
					'symbol_code' => sprintf('%1$s0%2$s00%3$s', $cSymbol, $cSep, $cCode),// symbol.'0'.separator.'00'.code
					'symbol_code_space' => sprintf('%1$s 0%2$s00 %3$s', $cSymbol, $cSep, $cCode),// symbol.' 0'.separator.'00 '.code
					'code_symbol' => sprintf('%3$s0%2$s00%1$s', $cSymbol, $cSep, $cCode),// code.'0'.separator.'00'.symbol
					'code_symbol_space' => sprintf('%3$s 0%2$s00 %1$s', $cSymbol, $cSep, $cCode),// code.' 0'.separator.'00 '.symbol
				)
			),
			array(
				'name' => __('Thousand separator', 'jigoshop'),
				'desc' => __('This sets the thousand separator of displayed prices.', 'jigoshop'),
				'tip' => '',
				'id' => 'jigoshop_price_thousand_sep',
				'type' => 'text',
			),
			array(
				'name' => __('Decimal separator', 'jigoshop'),
				'desc' => __('This sets the decimal separator of displayed prices.', 'jigoshop'),
				'tip' => '',
				'id' => 'jigoshop_price_decimal_sep',
				'type' => 'text',
			),
			array(
				'name' => __('Number of decimals', 'jigoshop'),
				'desc' => __('This sets the number of decimal points shown in displayed prices.', 'jigoshop'),
				'tip' => '',
				'id' => 'jigoshop_price_num_decimals',
				'type' => 'natural',
			),
			// Images tab
			array('type' => 'tab', 'name' => __('Images', 'jigoshop')),
			array(
				'name' => __('Image Options', 'jigoshop'),
				'type' => 'title',
				'desc' => sprintf(__('<p>Changing any of these settings will affect the dimensions of images used in your Shop. After changing these settings you may need to <a href="%s">regenerate your thumbnails</a>.</p><p>Crop: Leave unchecked to set the image size by resizing the image proportionally (that is, without distorting it). Leave checked to set the image size by hard cropping the image (either from the sides, or from the top and bottom).</p><p><strong>Note:</strong> Your images may not display in the size you choose below. This is because they may still be affected by CSS styles in your theme.', 'jigoshop'), 'http://wordpress.org/extend/plugins/regenerate-thumbnails/')
			),
			array('name' => __('Cropping Options', 'jigoshop'), 'type' => 'title', 'desc' => ''),
			array(
				'name' => __('Crop Tiny images', 'jigoshop'),
				'desc' => '',
				'tip' => __('Use No to set the image size by resizing the image proportionally (that is, without distorting it).<br />Use Yes to set the image size by hard cropping the image (either from the sides, or from the top and bottom).', 'jigoshop'),
				'id' => 'jigoshop_use_wordpress_tiny_crop',
				'type' => 'checkbox',
				'choices' => array(
					'no' => __('No', 'jigoshop'),
					'yes' => __('Yes', 'jigoshop'),
				),
			),
			array(
				'name' => __('Crop Thumbnail images', 'jigoshop'),
				'desc' => '',
				'tip' => __('Use No to set the image size by resizing the image proportionally (that is, without distorting it).<br />Use Yes to set the image size by hard cropping the image (either from the sides, or from the top and bottom).', 'jigoshop'),
				'id' => 'jigoshop_use_wordpress_thumbnail_crop',
				'type' => 'checkbox',
				'choices' => array(
					'no' => __('No', 'jigoshop'),
					'yes' => __('Yes', 'jigoshop'),
				),
			),
			array(
				'name' => __('Crop Catalog images', 'jigoshop'),
				'desc' => '',
				'tip' => __('Use No to set the image size by resizing the image proportionally (that is, without distorting it).<br />Use Yes to set the image size by hard cropping the image (either from the sides, or from the top and bottom).', 'jigoshop'),
				'id' => 'jigoshop_use_wordpress_catalog_crop',
				'type' => 'checkbox',
				'choices' => array(
					'no' => __('No', 'jigoshop'),
					'yes' => __('Yes', 'jigoshop'),
				),
			),
			array(
				'name' => __('Crop Large images', 'jigoshop'),
				'desc' => '',
				'tip' => __('Use No to set the image size by resizing the image proportionally (that is, without distorting it).<br />Use Yes to set the image size by hard cropping the image (either from the sides, or from the top and bottom).', 'jigoshop'),
				'id' => 'jigoshop_use_wordpress_featured_crop',
				'type' => 'checkbox',
				'choices' => array(
					'no' => __('No', 'jigoshop'),
					'yes' => __('Yes', 'jigoshop'),
				),
			),
			array('name' => __('Image Sizes', 'jigoshop'), 'type' => 'title', 'desc' => ''),
			array(
				'name' => __('Tiny Image Width', 'jigoshop'),
				'desc' => __('Default = 36px', 'jigoshop'),
				'tip' => __('Set the width of the small image used in the Cart, Checkout, Orders and Widgets.', 'jigoshop'),
				'id' => 'jigoshop_shop_tiny_w',
				'type' => 'natural',
			),
			array(
				'name' => __('Tiny Image Height', 'jigoshop'),
				'desc' => __('Default = 36px', 'jigoshop'),
				'tip' => __('Set the height of the small image used in the Cart, Checkout, Orders and Widgets.', 'jigoshop'),
				'id' => 'jigoshop_shop_tiny_h',
				'type' => 'natural',
			),
			array(
				'name' => __('Thumbnail Image Width', 'jigoshop'),
				'desc' => __('Default = 90px', 'jigoshop'),
				'tip' => __('Set the width of the thumbnail image for Single Product page extra images.', 'jigoshop'),
				'id' => 'jigoshop_shop_thumbnail_w',
				'type' => 'natural',
			),
			array(
				'name' => __('Thumbnail Image Height', 'jigoshop'),
				'desc' => __('Default = 90px', 'jigoshop'),
				'tip' => __('Set the height of the thumbnail image for Single Product page extra images.', 'jigoshop'),
				'id' => 'jigoshop_shop_thumbnail_h',
				'type' => 'natural',
			),
			array(
				'name' => __('Catalog Image Width', 'jigoshop'),
				'desc' => __('Default = 150px', 'jigoshop'),
				'tip' => __('Set the width of the catalog image for Shop, Categories, Tags, and Related Products.', 'jigoshop'),
				'id' => 'jigoshop_shop_small_w',
				'type' => 'natural',
			),
			array(
				'name' => __('Catalog Image Height', 'jigoshop'),
				'desc' => __('Default = 150px', 'jigoshop'),
				'tip' => __('Set the height of the catalog image for Shop, Categories, Tags, and Related Products.', 'jigoshop'),
				'id' => 'jigoshop_shop_small_h',
				'type' => 'natural',
			),
			array(
				'name' => __('Large Image Width', 'jigoshop'),
				'desc' => __('Default = 300px', 'jigoshop'),
				'tip' => __('Set the width of the Single Product page large or Featured image.', 'jigoshop'),
				'id' => 'jigoshop_shop_large_w',
				'type' => 'natural',
			),
			array(
				'name' => __('Large Image Height', 'jigoshop'),
				'desc' => __('Default = 300px', 'jigoshop'),
				'tip' => __('Set the height of the Single Product page large or Featured image.', 'jigoshop'),
				'id' => 'jigoshop_shop_large_h',
				'type' => 'natural',
			),
			// Products & Inventory tab
			array('type' => 'tab', 'name' => __('Products & Inventory', 'jigoshop')),
			array('name' => __('Product Options', 'jigoshop'), 'type' => 'title', 'desc' => ''),
			array(
				'name' => __('Enable SKU field', 'jigoshop'),
				'desc' => '',
				'tip' => __('Turning off the SKU field will give products an SKU of their post id.', 'jigoshop'),
				'id' => 'jigoshop_enable_sku',
				'type' => 'checkbox',
				'choices' => array(
					'no' => __('No', 'jigoshop'),
					'yes' => __('Yes', 'jigoshop'),
				),
			),
			array(
				'name' => __('Enable weight field', 'jigoshop'),
				'desc' => '',
				'tip' => '',
				'id' => 'jigoshop_enable_weight',
				'type' => 'checkbox',
				'choices' => array(
					'no' => __('No', 'jigoshop'),
					'yes' => __('Yes', 'jigoshop'),
				),
			),
			array(
				'name' => __('Weight Unit', 'jigoshop'),
				'desc' => '',
				'tip' => __("This controls what unit you will define weights in.", 'jigoshop'),
				'id' => 'jigoshop_weight_unit',
				'type' => 'radio',
				'choices' => array(
					'kg' => __('Kilograms', 'jigoshop'),
					'lbs' => __('Pounds', 'jigoshop'),
				),
			),
			array(
				'name' => __('Enable product dimensions', 'jigoshop'),
				'desc' => '',
				'tip' => '',
				'id' => 'jigoshop_enable_dimensions',
				'type' => 'checkbox',
				'choices' => array(
					'no' => __('No', 'jigoshop'),
					'yes' => __('Yes', 'jigoshop'),
				),
			),
			array(
				'name' => __('Dimensions Unit', 'jigoshop'),
				'desc' => '',
				'tip' => __('This controls what unit you will define dimensions in.', 'jigoshop'),
				'id' => 'jigoshop_dimension_unit',
				'type' => 'radio',
				'choices' => array(
					'cm' => __('centimeters', 'jigoshop'),
					'in' => __('inches', 'jigoshop'),
				),
			),
			array(
				'name' => __('Product thumbnail images per row', 'jigoshop'),
				'desc' => __('Default = 3', 'jigoshop'),
				'tip' => __('Determines how many extra product thumbnail images attached to a product to show on one row for the Single Product page.', 'jigoshop'),
				'id' => 'jigoshop_product_thumbnail_columns',
				'type' => 'number',
				'extra' => array(
					'min' => 1,
					'max' => 10,
					'step' => 1,
				),
			),
			array(
				'name' => __('Show related products', 'jigoshop'),
				'desc' => '',
				'tip' => __('To show or hide the related products section on a single product page.', 'jigoshop'),
				'id' => 'jigoshop_enable_related_products',
				'type' => 'checkbox',
				'choices' => array(
					'no' => __('No', 'jigoshop'),
					'yes' => __('Yes', 'jigoshop'),
				),
			),
			array('name' => __('Inventory Options', 'jigoshop'), 'type' => 'title', 'desc' => ''),
			array(
				'name' => __('Manage stock', 'jigoshop'),
				'desc' => __('If you are not managing stock, turn it off here to disable it in admin and on the front-end.', 'jigoshop'),
				'tip' => __('You can manage stock on a per-item basis if you leave this option on.', 'jigoshop'),
				'id' => 'jigoshop_manage_stock',
				'type' => 'checkbox',
				'choices' => array(
					'no' => __('No', 'jigoshop'),
					'yes' => __('Yes', 'jigoshop'),
				),
			),
			array(
				'name' => __('Show stock amounts', 'jigoshop'),
				'desc' => '',
				'tip' => __('Set to yes to allow customers to view the amount of stock available for a product.', 'jigoshop'),
				'id' => 'jigoshop_show_stock',
				'type' => 'checkbox',
				'choices' => array(
					'no' => __('No', 'jigoshop'),
					'yes' => __('Yes', 'jigoshop'),
				),
			),
			array(
				'name' => __('Notify on low stock', 'jigoshop'),
				'desc' => '',
				'id' => 'jigoshop_notify_low_stock',
				'type' => 'checkbox',
				'choices' => array(
					'no' => __('No', 'jigoshop'),
					'yes' => __('Yes', 'jigoshop'),
				),
			),
			array(
				'name' => __('Low stock threshold', 'jigoshop'),
				'desc' => '',
				'tip' => __('You will receive a notification as soon this threshold is hit (if notifications are turned on).', 'jigoshop'),
				'id' => 'jigoshop_notify_low_stock_amount',
				'type' => 'natural',
				'std' => '2',
			),
			array(
				'name' => __('Notify on out of stock', 'jigoshop'),
				'desc' => '',
				'id' => 'jigoshop_notify_no_stock',
				'type' => 'checkbox',
				'choices' => array(
					'no' => __('No', 'jigoshop'),
					'yes' => __('Yes', 'jigoshop'),
				),
			),
			array(
				'name' => __('Out of stock threshold', 'jigoshop'),
				'desc' => '',
				'tip' => __('You will receive a notification as soon this threshold is hit (if notifications are turned on).', 'jigoshop'),
				'id' => 'jigoshop_notify_no_stock_amount',
				'type' => 'natural',
			),
			array(
				'name' => __('Hide out of stock products', 'jigoshop'),
				'desc' => '',
				'tip' => __('For Yes: When the Out of Stock Threshold (above) is reached, the product visibility will be set to hidden so that it will not appear on the Catalog or Shop product lists.', 'jigoshop'),
				'id' => 'jigoshop_hide_no_stock_product',
				'type' => 'checkbox',
				'choices' => array(
					'no' => __('No', 'jigoshop'),
					'yes' => __('Yes', 'jigoshop'),
				),
			),
			// Tax tab
			array('type' => 'tab', 'name' => __('Tax', 'jigoshop')),
			array('name' => __('Tax Options', 'jigoshop'), 'type' => 'title', 'desc' => ''),
			array(
				'name' => __('Calculate Taxes', 'jigoshop'),
				'desc' => __('Only turn this off if you are exclusively selling non-taxable items.', 'jigoshop'),
				'tip' => __('If you are not calculating taxes then you can ignore all other tax options.', 'jigoshop'),
				'id' => 'jigoshop_calc_taxes',
				'type' => 'checkbox',
				'choices' => array(
					'no' => __('No', 'jigoshop'),
					'yes' => __('Yes', 'jigoshop'),
				),
			),
			array(
				'name' => __('Apply Taxes After Coupon', 'jigoshop'),
				'desc' => __('This will have no effect if Calculate Taxes is turned off.', 'jigoshop'),
				'tip' => __('If yes, taxes get applied after coupons. When no, taxes get applied before coupons.', 'jigoshop'),
				'id' => 'jigoshop_tax_after_coupon',
				'type' => 'checkbox',
				'choices' => array(
					'no' => __('No', 'jigoshop'),
					'yes' => __('Yes', 'jigoshop'),
				),
			),
			array(
				'name' => __('Catalog Prices include tax?', 'jigoshop'),
				'desc' => __('This will only apply to the Shop, Category and Product pages.', 'jigoshop'),
				'tip' => __('This will have no effect on the Cart, Checkout, Emails, or final Orders; prices are always shown with tax out.', 'jigoshop'),
				'id' => 'jigoshop_prices_include_tax',
				'type' => 'checkbox',
				'choices' => array(
					'no' => __('No', 'jigoshop'),
					'yes' => __('Yes', 'jigoshop'),
				),
			),
			array(
				'name' => __('Country to base taxes on', 'jigoshop'),
				'desc' => __('This option defines whether to use billing or shipping address to calculate taxes.', 'jigoshop'),
				'id' => 'jigoshop_country_base_tax',
				'type' => 'select',
				'choices' => array(
					'billing_country' => __('Billing', 'jigoshop'),
					'shipping_country' => __('Shipping', 'jigoshop'),
				),
			),
			array(
				'name' => __('Additional Tax classes', 'jigoshop'),
				'desc' => __('List 1 per line. This is in addition to the default <em>Standard Rate</em>.', 'jigoshop'),
				'tip' => __('List product and shipping tax classes here, e.g. Zero Tax, Reduced Rate.', 'jigoshop'),
				'id' => 'jigoshop_tax_classes',
				'type' => 'textarea',
			),
			array(
				'name' => __('Tax rates', 'jigoshop'),
				'desc' => '',
				'tip' => __('To avoid rounding errors, insert tax rates with 4 decimal places.', 'jigoshop'),
				'id' => 'jigoshop_tax_rates',
				'type' => 'tax_rates',
			),
			array('name' => __('Default options for new products', 'jigoshop'), 'type' => 'title', 'desc' => ''),
			array(
				'name' => __('Tax status', 'jigoshop'),
				'tip' => __('Whether new products should be taxable by default.', 'jigoshop'),
				'id' => 'jigoshop_tax_defaults_status',
				'type' => 'select',
				'std' => 'taxable',
				'choices' => array(
					'taxable' => __('Taxable', 'jigoshop'),
					'shipping' => __('Shipping', 'jigoshop'),
					'none' => __('None', 'jigoshop'),
				),
			),
			array(
				'name' => __('Tax classes', 'jigoshop'),
				'tip' => __('List of tax classes added by default to new products.', 'jigoshop'),
				'id' => 'jigoshop_tax_defaults_classes',
				'type' => 'user_defined',
				'display' => array($this, 'display_default_tax_classes'),
				'update' => array($this, 'update_default_tax_classes'),
			),
			// Shipping tab
			array('type' => 'tab', 'name' => __('Shipping', 'jigoshop')),
			array('name' => __('Shipping Options', 'jigoshop'), 'type' => 'title', 'desc' => ''),
			array(
				'name' => __('Enable Shipping', 'jigoshop'),
				'desc' => __('Only turn this off if you are <strong>not</strong> shipping items, or items have shipping costs included.', 'jigoshop'),
				'tip' => __('If turned off, this will also remove shipping address fields on the Checkout.', 'jigoshop'),
				'id' => 'jigoshop_calc_shipping',
				'type' => 'checkbox',
				'choices' => array(
					'no' => __('No', 'jigoshop'),
					'yes' => __('Yes', 'jigoshop'),
				),
			),
			array(
				'name' => __('Enable shipping calculator on cart', 'jigoshop'),
				'desc' => '',
				'tip' => '',
				'id' => 'jigoshop_enable_shipping_calc',
				'type' => 'checkbox',
				'choices' => array(
					'no' => __('No', 'jigoshop'),
					'yes' => __('Yes', 'jigoshop'),
				),
			),
			array(
				'name' => __('Only ship to billing address?', 'jigoshop'),
				'desc' => '',
				'tip' => __('When activated, Shipping address fields will not appear on the Checkout.', 'jigoshop'),
				'id' => 'jigoshop_ship_to_billing_address_only',
				'type' => 'checkbox',
				'choices' => array(
					'no' => __('No', 'jigoshop'),
					'yes' => __('Yes', 'jigoshop'),
				),
			),
			array(
				'name' => __('Checkout always shows Shipping fields?', 'jigoshop'),
				'desc' => __('This will have no effect if "Only ship to billing address" is activated.', 'jigoshop'),
				'tip' => __('When activated, Shipping address fields will appear by default on the Checkout.', 'jigoshop'),
				'id' => 'jigoshop_show_checkout_shipping_fields',
				'type' => 'checkbox',
				'choices' => array(
					'no' => __('No', 'jigoshop'),
					'yes' => __('Yes', 'jigoshop'),
				),
			),
			array(
				'name' => __('Available Shipping Methods', 'jigoshop'),
				'type' => 'title',
				'desc' => __('Please enable all of the Shipping Methods you wish to make available to your customers.', 'jigoshop'),
			),
			// Payment Gateways tab
			array('type' => 'tab', 'name' => __('Payment Gateways', 'jigoshop')),
			array('name' => __('Gateway Options', 'jigoshop'), 'type' => 'title', 'desc' => ''),
			array(
				'name' => __('Default Gateway', 'jigoshop'),
				'desc' => __('Only enabled gateways will appear in this list.', 'jigoshop'),
				'tip' => __('This will determine which gateway appears first in the Payment Methods list on the Checkout.', 'jigoshop'),
				'id' => 'jigoshop_default_gateway',
				'type' => 'default_gateway',
				'choices' => apply_filters('jigoshop_available_payment_gateways', array()),
			),
			array(
				'name' => __('Available gateways', 'jigoshop'),
				'type' => 'title',
				'desc' => __('Please enable all of the Payment Gateways you wish to make available to your customers.', 'jigoshop'),
			),
		);
	}

	/**
	 * Returns a named Jigoshop option
	 *
	 * @param   string  the name of the option to retrieve
	 * @param   mixed  the value to return if the option doesn't exist
	 * @return  mixed  the value of the option, null if no $default and option doesn't exist
	 * @since  1.12
	 */
	public function get($name, $default = null)
	{
		if(isset(self::$current_options[$name])){
			return apply_filters('jigoshop_get_option', self::$current_options[$name], $name, $default);
		} elseif(($old_option = get_option($name)) !== false){
			return apply_filters('jigoshop_get_option', $old_option, $name, $default);
		} elseif(isset($default)){
			return apply_filters('jigoshop_get_option', $default, $name, $default);
		} else {
			return null;
		}
	}

	/**
	 * Sets the entire Jigoshop current options
	 *
	 * @param array $options an array containing all the current Jigoshop option => value pairs to use
	 * @since 1.3
	 */
	private function set_current_options($options){
		self::$current_options = $options;
		if(!has_action('shutdown', array($this, 'update_options'))){
			add_action('shutdown', array($this, 'update_options'));
		}
	}

	/**
	 * Returns a named Jigoshop option
	 *
	 * @param string $name the name of the option to retrieve
	 * @param mixed $default the value to return if the option doesn't exist
	 * @return mixed the value of the option, null if no $default and doesn't exist
	 * @since  1.3
	 */
	public function get_option($name, $default = null){
		return $this->get($name, $default);
	}

	/**
	 * Sets a named Jigoshop option
	 *
	 * @param string $name the name of the option to set
	 * @param	mixed	$value the value to set
	 * @since	1.3
	 */
	public function set_option($name, $value){
		$this->set($name, $value);
	}

	/**
	 * Sets a named Jigoshop option
	 *
	 * @param   string  the name of the option to set
	 * @param  mixed  the value to set
	 * @since  1.12
	 */
	public function set($name, $value)
	{
		$this->get_current_options();

		if(isset($name)){
			self::$current_options[$name] = $value;
			if(!has_action('shutdown', array($this, 'update_options'))){
				add_action('shutdown', array($this, 'update_options'));
			}
		}
	}

	/**
	 * Deletes a named Jigoshop option
	 *
	 * @param string $name the name of the option to delete
	 * @return bool true for successful completion if option found, false otherwise
	 * @since	1.3
	 */
	public function delete_option($name){
		return $this->delete($name);
	}

	/**
	 * Deletes a named Jigoshop option
	 *
	 * @param   string  the name of the option to delete
	 * @return  bool  true for successful completion if option found, false otherwise
	 * @since  1.12
	 */
	public function delete($name)
	{
		$this->get_current_options();
		if(isset($name)){
			unset(self::$current_options[$name]);
			if(!has_action('shutdown', array($this, 'update_options'))){
				add_action('shutdown', array($this, 'update_options'));
			}

			return true;
		}

		return false;
	}

	/**
	 * Determines whether an Option exists
	 *
	 * @param string $name Option name.
	 * @return bool true for successful completion if option found, false otherwise
	 * @since 1.3
	 */
	public function exists_option($name){
		return $this->exists($name);
	}

	/**
	 * Determines whether an Option exists
	 *
	 * @param $name string the name of option to check for existence
	 * @return  bool  true for successful completion if option found, false otherwise
	 * @since  1.12
	 */
	public function exists($name)
	{
		$this->get_current_options();
		if(isset(self::$current_options[$name])){
			return true;
		}

		return false;
	}

	/**
	 * Install additional Tab's to Jigoshop Options
	 * Extensions would use this to add a new Tab for their own options
	 *
	 * NOTE: External code should not call this function any earlier than the WordPress 'init'
	 *       action hook in order for Jigoshop language translations to function properly
	 *
	 * @param	string $tab The name of the Tab ('tab'), eg. 'My Extension'
	 * @param	array	$options The array of options to install onto this tab
	 *
	 * @since	1.3
	 */
	public function install_external_options_tab($tab, $options){
		// only proceed with function if we have options to add
		if(empty($options)){
			return;
		}
		if(empty($tab)){
			return;
		}

		$our_options = $this->get_default_options();
		$our_options[] = array('type' => 'tab', 'name' => $tab);

		if(!empty($options)){
			foreach($options as $option){
				if(isset($option['id']) && !$this->exists($option['id'])){
					$this->add($option['id'], isset($option['std']) ? $option['std'] : '');
				}
				$our_options[] = $option;
			}
		}

		self::$default_options = $our_options;
	}

	/**
	 * Return the Jigoshop default options
	 *
	 * @return  array  the entire default options array is returned
	 * @since  1.3
	 */
	public function get_default_options(){
		if(empty(self::$default_options)){
			$this->set_default_options();
		}

		return self::$default_options;
	}

	/**
	 * Install additional default options for parsing onto a specific Tab
	 * Shipping methods, Payment gateways and Extensions would use this
	 *
	 * NOTE: External code should not call this function any earlier than the WordPress 'init'
	 *       action hook in order for Jigoshop language translations to function properly
	 *
	 * @param	string $tab The name of the Tab ('tab') to install onto
	 * @param	array	$options The array of options to install at the end of the current options on this Tab
	 *
	 * @since	1.3
	 */
	public function install_external_options_onto_tab($tab, $options){
		// only proceed with function if we have options to add
		if(empty($options)){
			return;
		}
		if(empty($tab)){
			return;
		}

		$our_options = $this->get_default_options();
		$first_index = -1;
		$second_index = -1;
		foreach($our_options as $index => $option){
			if($option['type'] <> 'tab'){
				continue;
			}
			if($option['name'] == $tab){
				$first_index = $index;
				continue;
			}
			if($first_index >= 0){
				$second_index = $index;
				break;
			}
		}

		if($second_index < 0){
			$second_index = count($our_options);
		}

		/*** get the start of the array ***/
		$start = array_slice($our_options, 0, $second_index);
		/*** get the end of the array ***/
		$end = array_slice($our_options, $second_index);
		/*** add the new elements to the array ***/
		foreach($options as $option){
			if(isset($option['id']) && !$this->exists($option['id'])){
				$this->add($option['id'], isset($option['std']) ? $option['std'] : '');
			}
			$start[] = $option;
		}

		/*** glue them back together ***/
		self::$default_options = array_merge($start, $end);
	}

	/**
	 * Install additional default options for parsing after a specific option ID
	 * Extensions would use this
	 *
	 * NOTE: External code should not call this function any earlier than the WordPress 'init'
	 *       action hook in order for Jigoshop language translations to function properly
	 *
	 * @param	string $insert_after_id	The name of the ID  to install -after-
	 * @param	array	$options The array of options to install
	 * @since	1.3
	 */
	public function install_external_options_after_id($insert_after_id, $options){
		// only proceed with function if we have options to add
		if(empty($options)){
			return;
		}
		if(empty($insert_after_id)){
			return;
		}

		$our_options = $this->get_default_options();
		$first_index = -1;
		foreach($our_options as $index => $option){
			if(!isset($option['id']) || $option['id'] <> $insert_after_id){
				continue;
			}
			$first_index = $index;
			break;
		}

		/*** get the start of the array ***/
		$start = array_slice($our_options, 0, $first_index + 1);
		/*** get the end of the array ***/
		$end = array_slice($our_options, $first_index + 1);
		/*** add the new elements to the array ***/
		foreach($options as $option){
			if(isset($option['id']) && !$this->exists($option['id'])){
				$this->add($option['id'], isset($option['std']) ? $option['std'] : '');
			}
			$start[] = $option;
		}

		/*** glue them back together ***/
		self::$default_options = array_merge($start, $end);
	}

	public function generate_defaults_emails(){
		return '<button type="button" onClick="parent.location=\'admin.php?page=jigoshop_settings&tab=general&install_emails=1\'">'.__('Generate Defaults', 'jigoshop').'</button>';
	}

	public function display_default_tax_classes()
	{
		$tax = new jigoshop_tax();
		$classes = $tax->get_tax_classes();
		$defaults = Jigoshop_Base::get_options()->get('jigoshop_tax_defaults_classes', array('*'));

		ob_start();
		echo Jigoshop_Forms::checkbox(array(
			'id' => 'jigoshop_tax_defaults_class_standard',
			'name' => 'jigoshop_tax_defaults_classes[*]',
			'label' => __('Standard', 'jigoshop'),
			'value' => in_array('*', $defaults),
		));

		foreach ($classes as $class) {
			$value = sanitize_title($class);
			echo Jigoshop_Forms::checkbox(array(
				'id' => 'jigoshop_tax_defaults_class_'.$value,
				'name' => 'jigoshop_tax_defaults_classes['.$value.']',
				'label' => __($class, 'jigoshop'),
				'value' => in_array($value, $defaults),
			));
		}

		return ob_get_clean();
	}

	public function update_default_tax_classes()
	{
		if (!isset($_POST['jigoshop_tax_defaults_classes'])) {
			return array();
		}

		$classes = array();
		foreach ($_POST['jigoshop_tax_defaults_classes'] as $class => $value) {
			$classes[] = $class;
		}

		return $classes;
	}

	public function jigoshop_deprecated_options(){
		echo '<div class="error"><p>'.sprintf(__('The following items, from one or more extensions, have tried to add Jigoshop Settings in a manner that is no longer supported as of Jigoshop 1.3. (%s)', 'jigoshop').'</p></div>', implode(', ', $this->bad_extensions));
	}
}
