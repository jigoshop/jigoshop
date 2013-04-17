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
 * @copyright   Copyright © 2011-2013 Jigoshop.
 * @license     http://jigoshop.com/license/commercial-edition
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
	 * @param   none
	 * @return  Void
	 *
	 * @since	1.3
	 */
	public function __construct() {

		self::$current_options = get_option( JIGOSHOP_OPTIONS );	// load existing from database, false if none

		if ( false === self::$current_options ) {

			if ( ! $this->need_to_upgrade_from_123_to_130() ) {     // should be from a fresh install

				$current_options = array();

				foreach ( $this->get_default_options() as $setting ) :
					if ( isset( $setting['id'] )) {
						$current_options[$setting['id']] = $setting['std'];
					}
				endforeach;

				self::$current_options = $current_options;
				update_option( JIGOSHOP_OPTIONS, $current_options );

			}

		}

		self::$default_options = $this->get_default_options();

	}


	/**
	 * Special case function that's run if no 'jigoshop_options' available in Database
	 * Typically encountered when first run on upgrade from version 1.2.3 to version 1.3
	 * when this class is first implemented.
	 *
	 * Finds all separate options from 1.2.3 and installs them as our current options with a few changes
	 * Sets a 'jigoshop_upgraded_from_123' flag option to note that this has been done.
	 *
	 * This has to happen here instead of an 'upgrade' script due to 'update_option' changing a few defaults
	 * and the old existing options are then altered prior to the 'upgrade'.
	 *
	 * @param   none
	 * @return  true if upgrade was needed, false otherwise
	 *
	 * @since	1.3
	 */
	private function need_to_upgrade_from_123_to_130() {

		global $wpdb;

		$version = get_site_option( 'jigoshop_db_version' );
		$upgraded = get_option( 'jigoshop_upgraded_from_123' );
		if ( $version !== false && $version < 1207160 && $upgraded === false ) {

			$transfer_options = array();
			$options_in_use = $wpdb->get_results(
				$wpdb->prepare( "SELECT * FROM {$wpdb->options} WHERE option_name LIKE 'jigoshop_%%';" ));
			foreach ( $options_in_use as $index => $setting ) {
				if ( $setting->option_name == 'jigoshop_options' ) continue;
				if ( $setting->option_name == 'jigoshop_db_version' ) continue;
				// this will add all settings into the new api, even if they will not be called from there.
				// eg. enabled plugins may not have utilized the new infrastructure yet.
				$transfer_options[$setting->option_name] = get_option( $setting->option_name );
				if ( $setting->option_name == 'jigoshop_display_totals_tax' ) {
					$current = get_option( $setting->option_name );
					if ( false !== $current ) {
						if ( $current == 'including' ) :
							$transfer_options[$setting->option_name] = 'yes';
						else :
							$transfer_options[$setting->option_name] = 'no';
						endif;
					}
				}
			}

			if ( $transfer_options['jigoshop_paypal_testmode'] == 'yes' ) {
				$transfer_options['jigoshop_sandbox_email'] = $transfer_options['jigoshop_paypal_email'];
				$transfer_options['jigoshop_paypal_email'] = '';
			}

			$transfer_options['jigoshop_use_beta_version'] = 'no';
			$transfer_options['jigoshop_reset_pending_orders'] = 'no';
			$transfer_options['jigoshop_downloads_require_login'] = 'no';
			$transfer_options['jigoshop_frontend_with_theme_css'] = 'no';

			$transfer_options['jigoshop_upgraded_from_123'] = 'yes';

			self::$current_options = $transfer_options;
 			update_option( JIGOSHOP_OPTIONS, $transfer_options );
 			update_option( 'jigoshop_upgraded_from_123', 'yes' );

 			return true;

		} else {    // fresh install?

			return false;

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
	public function update_options() {
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
	 * @since	1.3
	 */
	public function add_option( $name, $value ) {
		// take care of keeping the old name updated when setting new options
		add_option($name, $value);

		$this->get_current_options();
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
	 * @since	1.3
	 */
	public function get_option( $name, $default = null ) {

		$old_option = get_option($name);

		if ( isset( self::$current_options[$name] )) :
			return apply_filters( 'jigoshop_get_option', self::$current_options[$name], $name, $default);
		elseif ( isset( $old_option ) && $old_option !== false ) :
			return apply_filters( 'jigoshop_get_option', $old_option, $name, $default);
		elseif ( isset( $default )) :
			return apply_filters( 'jigoshop_get_option', $default, $name, $default);
		else :
			return null;
		endif;

	}

	/**
	 * Sets a named Jigoshop option
	 *
	 * @param   string	the name of the option to set
	 * @param	mixed	the value to set
	 *
	 * @since	1.3
	 */
	public function set_option( $name, $value ) {

		// take care of keeping the old name updated when setting new options
		update_option($name, $value);

		$this->get_current_options();
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
	 * @since	1.3
	 */
	public function delete_option( $name ) {
		$this->get_current_options();
		if ( isset( $name )) {
			unset( self::$current_options[$name] );
			add_action( 'shutdown', array( __CLASS__, 'update_options' ));
			return true;
		}
		return false;
	}


	/**
	 * Determines whether an Option exists
	 *
	 * @return	bool	true for successful completion if option found, false otherwise
	 *
	 * @since	1.3
	 */
	public function exists_option( $name ) {
		$this->get_current_options();
		if ( isset( self::$current_options[$name] )) return true;
		else return false;
	}


	/**
	 * Install additional Tab's to Jigoshop Options
	 * Extensions would use this to add a new Tab for their own options
	 *
	 * NOTE: External code should not call this function any earlier than the WordPress 'init'
	 *       action hook in order for Jigoshop language translations to function properly
	 *
	 * @param	string	The name of the Tab ('tab'), eg. 'My Extension'
	 * @param	array	The array of options to install onto this tab
	 *
	 * @since	1.3
	 */
	public function install_external_options_tab( $tab, $options ) {

		// only proceed with function if we have options to add
		if ( empty( $options )) return;
		if ( empty( $tab )) return;

		$our_options = $this->get_default_options();
		$our_options[] = array( 'type' => 'tab', 'name' => $tab );
		if ( ! empty( $options )) foreach ( $options as $id => $option ) {
			if ( isset( $option['id'] ) && !$this->exists_option( $option['id'] )) {
				$this->add_option( $option['id'], isset( $option['std'] ) ? $option['std'] : '' );
			}
			$our_options[] = $option;
		}
		self::$default_options = $our_options;

	}


	/**
	 * Install additional default options for parsing onto a specific Tab
	 * Shipping methods, Payment gateways and Extensions would use this
	 *
	 * NOTE: External code should not call this function any earlier than the WordPress 'init'
	 *       action hook in order for Jigoshop language translations to function properly
	 *
	 * @param	string	The name of the Tab ('tab') to install onto
	 * @param	array	The array of options to install at the end of the current options on this Tab
	 *
	 * @since	1.3
	 */
	public function install_external_options_onto_tab( $tab, $options ) {

		// only proceed with function if we have options to add
		if ( empty( $options )) return;
		if ( empty( $tab )) return;

		$our_options = $this->get_default_options();
		$first_index = -1;
		$second_index = -1;
		foreach ( $our_options as $index => $option ) {
			if ( $option['type'] <> 'tab' ) continue;
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
			if ( isset( $option['id'] ) && !$this->exists_option( $option['id'] )) {
				$this->add_option( $option['id'], isset( $option['std'] ) ? $option['std'] : '' );
			}
			$start[] = $option;
		}
		/*** glue them back together ***/
		self::$default_options = array_merge( $start, $end );
 	}


	/**
	 * Install additional default options for parsing after a specific option ID
	 * Extensions would use this
	 *
	 * NOTE: External code should not call this function any earlier than the WordPress 'init'
	 *       action hook in order for Jigoshop language translations to function properly
	 *
	 * @param	string	The name of the ID  to install -after-
	 * @param	array	The array of options to install
	 *
	 * @since	1.3
	 */
	public function install_external_options_after_id( $insert_after_id, $options ) {

		// only proceed with function if we have options to add
		if ( empty( $options )) return;
		if ( empty( $insert_after_id )) return;

		$our_options = $this->get_default_options();
		$first_index = -1;
		foreach ( $our_options as $index => $option ) {
			if ( ! isset( $option['id'] ) || $option['id'] <> $insert_after_id ) continue;
			$first_index = $index;
			break;
		}
		/*** get the start of the array ***/
		$start = array_slice( $our_options, 0, $first_index+1 );
		/*** get the end of the array ***/
		$end = array_slice( $our_options, $first_index+1 );
		/*** add the new elements to the array ***/
		foreach ( $options as $option ) {
			if ( isset( $option['id'] ) && !$this->exists_option( $option['id'] )) {
				$this->add_option( $option['id'], isset( $option['std'] ) ? $option['std'] : '' );
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
	 * @since	1.3
	 */
	public function get_current_options() {
		if ( empty( self::$current_options )) {
			if ( empty( self::$default_options )) $this->set_default_options();
			else $this->set_current_options( self::$default_options );
		}
		return self::$current_options;
	}


	/**
	 * Sets the entire Jigoshop current options
	 *
	 * @param   array	an array containing all the current Jigoshop option => value pairs to use
	 * @return  Void
	 *
	 * @since	1.3
	 */
	private function set_current_options( $options ) {
		self::$current_options = $options;
		add_action( 'shutdown', array( __CLASS__, 'update_options' ));
	}


	/**
	 * Return the Jigoshop default options
	 *
	 * @param   none
	 * @return  array	the entire default options array is returned
	 *
	 * @since	1.3
	 */
	public function get_default_options() {
		if ( empty( self::$default_options )) $this->set_default_options();
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
	 * @since	1.3
	 *
	 */
	private function set_default_options() {

		self::$default_options = array();

		/**
		 * Shop Tab
		 *------------------------------------------------------------------------------------------
		*/
		self::$default_options[] = array( 'type' => 'tab', 'name' => __('Shop', 'jigoshop') );

		self::$default_options[] = array( 'name' => __( 'Shop Options', 'jigoshop' ), 'type' => 'title', 'desc' => '' );

		self::$default_options[] = array(
			'name'		=> __( 'Base Country/Region', 'jigoshop' ),
			'desc' 		=> '',
			'tip' 		=> __( 'This is the base country for your business. Tax rates will be based on this country.', 'jigoshop' ),
			'id' 		=> 'jigoshop_default_country',
			'std' 		=> 'GB',
			'type' 		=> 'single_select_country'
		);

		$symbols = jigoshop::currency_symbols();
		$countries = jigoshop::currency_countries();
		$currencies = array();
		foreach ( $countries as $key => $country ) {
			$currencies[$key] = $country . ' (' . $symbols[$key] . ')';
		}
		$currencies = apply_filters('jigoshop_currencies', $currencies );
		self::$default_options[] = array(
			'name'		=> __('Currency', 'jigoshop'),
			'desc' 		=> '',
			'tip' 		=> __("This controls what currency the prices are listed with in the Catalog, and which currency PayPal, and other gateways, will take payments in.", 'jigoshop'),
			'id' 		=> 'jigoshop_currency',
			'std' 		=> 'GBP',
			'type' 		=> 'select',
			'choices'	=> $currencies
		);

		self::$default_options[] = array(
			'name'		=> __( 'Allowed Countries', 'jigoshop' ),
			'desc' 		=> '',
			'tip' 		=> __( 'These are countries that you are willing to ship to.', 'jigoshop' ),
			'id' 		=> 'jigoshop_allowed_countries',
			'std' 		=> 'all',
			'type' 		=> 'select',
			'choices'	=> array(
				'all'			=> __( 'All Countries', 'jigoshop' ),
				'specific'		=> __( 'Specific Countries', 'jigoshop' )
			)
		);

		self::$default_options[] = array(
			'name'		=> __( 'Specific Countries', 'jigoshop' ),
			'desc' 		=> '',
			'tip' 		=> '',
			'id' 		=> 'jigoshop_specific_allowed_countries',
			'std' 		=> '',
			'type' 		=> 'multi_select_countries'
		);

		self::$default_options[] = array(
			'name'		=> __( 'Demo store', 'jigoshop' ),
			'desc' 		=> '',
			'tip' 		=> __( 'Enable this option to show a banner at the top of every page stating this shop is currently in testing mode.', 'jigoshop' ),
			'id' 		=> 'jigoshop_demo_store',
			'std' 		=> 'no',
			'type' 		=> 'checkbox',
			'choices'	=> array(
				'no'			=> __('No', 'jigoshop'),
				'yes'			=> __('Yes', 'jigoshop')
			)
		);

		self::$default_options[] = array( 'name' => __( 'Invoicing', 'jigoshop' ), 'type' => 'title', 'desc' => '' );

		self::$default_options[] = array(
			'name'		=> __( 'Company Name', 'jigoshop' ),
			'desc' 		=> '',
			'tip' 		=> __( 'Setting your company name will enable us to print it out on your invoice emails. Leave blank to disable.', 'jigoshop' ),
			'id' 		=> 'jigoshop_company_name',
			'std' 		=> '',
			'type' 		=> 'text'
		);

		self::$default_options[] = array(
			'name'		=> __( 'Tax Registration Number', 'jigoshop' ),
			'desc' 		=> __( 'Add your tax registration label before the registration number and it will be printed as well. eg. <code>VAT Number: 88888888</code>', 'jigoshop' ),
			'tip' 		=> __( 'Setting your tax number will enable us to print it out on your invoice emails. Leave blank to disable.', 'jigoshop' ),
			'id' 		=> 'jigoshop_tax_number',
			'std' 		=> '',
			'type' 		=> 'text'
		);

		self::$default_options[] = array(
			'name'		=> __( 'Address Line1', 'jigoshop' ),
			'desc' 		=> '',
			'tip' 		=> __( 'Setting your address will enable us to print it out on your invoice emails. Leave blank to disable.', 'jigoshop' ),
			'id' 		=> 'jigoshop_address_line1',
			'std' 		=> '',
			'type' 		=> 'longtext'
		);

		self::$default_options[] = array(
			'name' =>	__( 'Address Line2', 'jigoshop' ),
			'desc' 		=> '',
			'tip' 		=> __( 'If address line1 is not set, address line2 will not display even if you put a value in it. Setting your address will enable us to print it out on your invoice emails. Leave blank to disable.', 'jigoshop' ),
			'id' 		=> 'jigoshop_address_line2',
			'std' 		=> '',
			'type' 		=> 'longtext'
		);

		self::$default_options[] = array(
			'name'		=> __( 'Company Phone', 'jigoshop' ),
			'desc' 		=> '',
			'tip' 		=> __( 'Setting your company phone number will enable us to print it out on your invoice emails. Leave blank to disable.', 'jigoshop' ),
			'id' 		=> 'jigoshop_company_phone',
			'std' 		=> '',
			'type' 		=> 'text'
		);


		self::$default_options[] = array(
			'name'		=> __( 'Company Email', 'jigoshop' ),
			'desc' 		=> '',
			'tip' 		=> __( 'Setting your company email will enable us to print it out on your invoice emails. Leave blank to disable.', 'jigoshop' ),
			'id' 		=> 'jigoshop_company_email',
			'std' 		=> '',
			'type' 		=> 'email'
		);

		self::$default_options[] = array( 'name' => __('Permalinks', 'jigoshop'), 'type' => 'title', 'desc' => '' );

		self::$default_options[] = array(
			'name'		=> __('Prepend shop categories and tags with base page','jigoshop'),
			'desc' 		=> '',
			'tip' 		=> __('This will only apply to tags &amp; categories.<br/>Enabled: http://yoursite.com / product_category / YourCategory<br/>Disabled: http://yoursite.com / base_page / product_category / YourCategory', 'jigoshop'),
			'id' 		=> 'jigoshop_prepend_shop_page_to_urls',
			'type' 		=> 'checkbox',
			'std' 		=> 'no',
			'choices'	=> array(
				'no'			=> __('No', 'jigoshop'),
				'yes'			=> __('Yes', 'jigoshop')
			)
		);

		self::$default_options[] = array(
			'name'		=> __('Prepend product permalinks with shop base page','jigoshop'),
			'desc' 		=> '',
			'tip' 		=> '',
			'id' 		=> 'jigoshop_prepend_shop_page_to_product',
			'type' 		=> 'checkbox',
			'std' 		=> 'no',
			'choices'	=> array(
				'no'			=> __('No', 'jigoshop'),
				'yes'			=> __('Yes', 'jigoshop')
			)
		);

		self::$default_options[] = array(
			'name'		=> __('Prepend product permalinks with product category','jigoshop'),
			'desc' 		=> '',
			'tip' 		=> '',
			'id' 		=> 'jigoshop_prepend_category_to_product',
			'type' 		=> 'checkbox',
			'std' 		=> 'no',
			'choices'	=> array(
				'no'			=> __('No', 'jigoshop'),
				'yes'			=> __('Yes', 'jigoshop')
			)
		);

		self::$default_options[] = array(
			'name'		=> __('Product category slug','jigoshop'),
			'desc' 		=> '',
			'tip' 		=> __('Slug displayed in product category URLs. Leave blank to use default "product-category"', 'jigoshop'),
			'id' 		=> 'jigoshop_product_category_slug',
			'type' 		=> 'text',
			'std' 		=> 'product-category'
		);

		self::$default_options[] = array(
			'name'		=> __('Product tag slug','jigoshop'),
			'desc' 		=> '',
			'tip' 		=> __('Slug displayed in product tag URLs. Leave blank to use default "product-tag"', 'jigoshop'),
			'id' 		=> 'jigoshop_product_tag_slug',
			'type' 		=> 'text',
			'std' 		=> 'product-tag'
		);

		/**
		 * General Tab
		 *------------------------------------------------------------------------------------------
		*/
		self::$default_options[] = array( 'type' => 'tab', 'name' => __('General', 'jigoshop') );

		self::$default_options[] = array( 'name' => __('General Options', 'jigoshop'), 'type' => 'title', 'desc' => '' );

		self::$default_options[] = array(
			'name'		=> __('Jigoshop email address','jigoshop'),
			'desc' 		=> '',
			'tip' 		=> __('The email address used to send all Jigoshop related emails, such as order confirmations and notices.  This may be different than your Company email address on "Shop Tab -> Invoicing".','jigoshop'),
			'id' 		=> 'jigoshop_email',
			'type' 		=> 'email',
			'std' 		=> get_option('admin_email')
		);

		self::$default_options[] = array(
			'name'		=> __('Cart shows "Return to Shop" button','jigoshop'),
			'desc' 		=> '',
			'tip' 		=> __('Enabling this setting will display a "Return to Shop" button on the Cart page along with the "Continue to Checkout" button.','jigoshop'),
			'id' 		=> 'jigoshop_cart_shows_shop_button',
			'std' 		=> 'yes',
			'type' 		=> 'checkbox',
			'choices'	=> array(
				'no'			=> __('No', 'jigoshop'),
				'yes'			=> __('Yes', 'jigoshop')
			)
		);

		self::$default_options[] = array(
			'name'		=> __('After adding product to cart','jigoshop'),
			'desc' 		=> '',
			'tip' 		=> __('Define what should happen when a user clicks on &#34;Add to Cart&#34; on any product or page.','jigoshop'),
			'id' 		=> 'jigoshop_redirect_add_to_cart',
			'std' 		=> 'same_page',
			'type' 		=> 'radio',
			'extra' 	=> array( 'vertical' ),
			'choices'	=> array(
				'same_page'		=> __('Stay on the same page', 'jigoshop'),
				'to_checkout'	=> __('Redirect to Checkout', 'jigoshop'),
				'to_cart'		=> __('Redirect to Cart', 'jigoshop'),
			)
		);

		self::$default_options[] = array(
			'name'		=> __('Reset pending Orders','jigoshop'),
			'desc' 		=> __("Change all 'Pending' Orders older than one month to 'On Hold'",'jigoshop'),
			'tip' 		=> __("For customers that have not completed the Checkout process or haven't paid for an Order after a period of time, this will reset the Order to On Hold allowing the Shop owner to take action.  WARNING: For the first use on an existing Shop this setting <em>can</em> generate a <strong>lot</strong> of email!",'jigoshop'),
			'id' 		=> 'jigoshop_reset_pending_orders',
			'std' 		=> 'no',
			'type' 		=> 'checkbox',
			'choices'	=> array(
				'no'			=> __('No', 'jigoshop'),
				'yes'			=> __('Yes', 'jigoshop')
			)
		);

		self::$default_options[] = array(
			'name'		=> __('Enforce login for downloads','jigoshop'),
			'desc' 		=> '',
			'tip' 		=> __('If a guest purchases a download, the guest can still download a link without logging in. We recommend disabling guest purchases if you enable this option.','jigoshop'),
			'id' 		=> 'jigoshop_downloads_require_login',
			'std' 		=> 'no',
			'type' 		=> 'checkbox',
			'choices'	=> array(
				'no'			=> __('No', 'jigoshop'),
				'yes'			=> __('Yes', 'jigoshop')
			)
		);

		self::$default_options[] = array(
			'name'		=> __('Disable Jigoshop frontend.css','jigoshop'),
			'desc' 		=> __('(The next option below will have no effect if this one is disabled)','jigoshop'),
			'tip' 		=> __('Useful if you want to disable Jigoshop styles and theme it yourself via your theme.','jigoshop'),
			'id' 		=> 'jigoshop_disable_css',
			'std' 		=> 'no',
			'type' 		=> 'checkbox',
			'choices'	=> array(
				'no'			=> __('No', 'jigoshop'),
				'yes'			=> __('Yes', 'jigoshop')
			)
		);

		self::$default_options[] = array(
			'name'		=> __('Include extra theme styles with Jigoshop frontend.css','jigoshop'),
			'desc' 		=> '',
			'tip' 		=> __("With this option <em>on</em>, Jigoshop's default frontend.css will still load, and any extra bits found in 'theme/jigoshop/style.css' for over-rides will also be loaded.",'jigoshop'),
			'id' 		=> 'jigoshop_frontend_with_theme_css',
			'std' 		=> 'no',
			'type' 		=> 'checkbox',
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
			'type' 		=> 'checkbox',
			'choices'	=> array(
				'no'			=> __('No', 'jigoshop'),
				'yes'			=> __('Yes', 'jigoshop')
			)
		);

		self::$default_options[] = array( 'name' => __('Checkout page', 'jigoshop'), 'type' => 'title', 'desc' => '' );

		self::$default_options[] = array(
			'name'		=> __('Validate postal/zip codes','jigoshop'),
			'desc' 		=> '',
			'tip' 		=> __('Enabling this setting will force proper postcodes to be entered by a customer for a country.','jigoshop'),
			'id' 		=> 'jigoshop_enable_postcode_validating',
			'std' 		=> 'no',
			'type' 		=> 'checkbox',
			'choices'	=> array(
				'no'			=> __('No', 'jigoshop'),
				'yes'			=> __('Yes', 'jigoshop')
			)
		);

		self::$default_options[] = array(
			'name'		=> __('Show verify information message','jigoshop'),
			'desc' 		=> '',
			'tip' 		=> __('Enabling this setting will display a message at the bottom of the Checkout asking customers to verify all their informatioin is correctly entered before placing their Order.  This is useful in particular for Countries that have states to ensure the correct shipping state is selected.','jigoshop'),
			'id' 		=> 'jigoshop_verify_checkout_info_message',
			'std' 		=> 'yes',
			'type' 		=> 'checkbox',
			'choices'	=> array(
				'no'			=> __('No', 'jigoshop'),
				'yes'			=> __('Yes', 'jigoshop')
			)
		);

		self::$default_options[] = array(
			'name'		=> __('Show EU VAT reduction message','jigoshop'),
			'desc' 		=> __('This will only apply to EU Union based Shops.','jigoshop'),
			'tip' 		=> __('Enabling this setting will display a message at the bottom of the Checkout informing the customer that EU VAT will not be removed until the Order is placed and only if they have provided a valid EU VAT Number.','jigoshop'),
			'id' 		=> 'jigoshop_eu_vat_reduction_message',
			'std' 		=> 'yes',
			'type' 		=> 'checkbox',
			'choices'	=> array(
				'no'			=> __('No', 'jigoshop'),
				'yes'			=> __('Yes', 'jigoshop')
			)
		);

		self::$default_options[] = array(
			'name'		=> __('Allow guest purchases','jigoshop'),
			'desc' 		=> '',
			'tip' 		=> __('Enabling this setting will allow users to checkout without registering or signing up. Otherwise, users must be signed in or must sign up to checkout.','jigoshop'),
			'id' 		=> 'jigoshop_enable_guest_checkout',
			'std' 		=> 'yes',
			'type' 		=> 'checkbox',
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
			'type' 		=> 'checkbox',
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
			'type' 		=> 'checkbox',
			'choices'	=> array(
				'no'			=> __('No', 'jigoshop'),
				'yes'			=> __('Yes', 'jigoshop')
			)
		);

		self::$default_options[] = array(
			'name'		=> __('Force SSL on checkout','jigoshop'),
			'desc' 		=> '',
			'tip' 		=> __('This will load your checkout page with https://. An SSL certificate is <strong>required</strong> if you choose yes. Contact your hosting provider for more information on SSL Certs.','jigoshop'),
			'id' 		=> 'jigoshop_force_ssl_checkout',
			'std' 		=> 'no',
			'type' 		=> 'checkbox',
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
			'type' 		=> 'checkbox',
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
		self::$default_options[] = array( 'type' => 'tab', 'name' => __('Pages', 'jigoshop') );

		self::$default_options[] = array( 'name' => __('Page configurations', 'jigoshop'), 'type' => 'title', 'desc' => '' );

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
			'desc' 		=> __('Shortcode to place on page: <code>[jigoshop_view_order]</code><br/>Default parent page: My Account','jigoshop'),
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
		self::$default_options[] = array( 'type' => 'tab', 'name' => __('Catalog &amp; Pricing', 'jigoshop') );

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
			'name'		=> __('Catalog product buttons show','jigoshop'),
			'desc' 		=> '',
			'tip' 		=> __('This will determine the type of button and the action it will use when clicked on the Shop and Category product listings.  You can also set it to use no button.','jigoshop'),
			'id' 		=> 'jigoshop_catalog_product_button',
			'std' 		=> 'add',
			'type' 		=> 'radio',
			'choices'	=> array(
				'add'           => __('Add to Cart', 'jigoshop'),
				'view'          => __('View Product', 'jigoshop'),
				'none'          => __('No Button', 'jigoshop')
			)
		);

		self::$default_options[] = array(
			'name'		=> __('Sort products in catalog by','jigoshop'),
			'desc' 		=> '',
			'tip' 		=> __('Determines the display sort order of products for the Shop, Categories, and Tag pages.','jigoshop'),
			'id' 		=> 'jigoshop_catalog_sort_orderby',
			'std' 		=> 'post_date',
			'type' 		=> 'radio',
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
			'type' 		=> 'radio',
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
			'type' 		=> 'range',
			'extra'		=> array(
				'min'			=> 1,
				'max'			=> 10,
				'step'			=> 1
			)
		);

		self::$default_options[] = array(
			'name'		=> __('Catalog products per page','jigoshop'),
			'desc' 		=> __('Default = 12','jigoshop'),
			'tip' 		=> __('Determines how many products to display on Shop, Category and Tag pages before needing next and previous page navigation.','jigoshop'),
			'id' 		=> 'jigoshop_catalog_per_page',
			'std' 		=> '12',
			'type' 		=> 'range',
			'extra'		=> array(
				'min'			=> 1,
				'max'			=> 100,
				'step'			=> 1
			)
		);

		self::$default_options[] = array( 'name' => __('Pricing Options', 'jigoshop'), 'type' => 'title', 'desc' => '' );

		if ( function_exists('get_jigoshop_currency_symbol') ) $cSymbol = get_jigoshop_currency_symbol();
		else $cSymbol = '';
		$cCode = $this->get_option( 'jigoshop_currency' ) ? $this->get_option( 'jigoshop_currency' ) : 'GBP';
		$cSep = $this->get_option( 'jigoshop_price_decimal_sep' ) ? $this->get_option( 'jigoshop_price_decimal_sep' ) : '.';

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
			'type' 		=> 'natural',
		);

		/**
		 * Images Tab
		 *------------------------------------------------------------------------------------------
		*/
		self::$default_options[] = array( 'type' => 'tab', 'name' => __('Images', 'jigoshop') );

		self::$default_options[] = array( 'name' => __('Image Options', 'jigoshop'), 'type' => 'title', 'desc' => sprintf( __('<p>Changing any of these settings will affect the dimensions of images used in your Shop. After changing these settings you may need to <a href="%s">regenerate your thumbnails</a>.</p><p>Crop: Leave unchecked to set the image size by resizing the image proportionally (that is, without distorting it). Leave checked to set the image size by hard cropping the image (either from the sides, or from the top and bottom).</p><p><strong>Note:</strong> Your images may not display in the size you choose below. This is because they may still be affected by CSS styles in your theme.', 'jigoshop'), 'http://wordpress.org/extend/plugins/regenerate-thumbnails/') );

		self::$default_options[] = array( 'name' => __('Cropping Options', 'jigoshop'), 'type' => 'title', 'desc' => '' );

		self::$default_options[] = array(
			'name'		=> __('Crop Tiny images','jigoshop'),
			'desc' 		=> '',
			'tip' 		=> __('Use No to set the image size by resizing the image proportionally (that is, without distorting it).<br />Use Yes to set the image size by hard cropping the image (either from the sides, or from the top and bottom).','jigoshop'),
			'id' 		=> 'jigoshop_use_wordpress_tiny_crop',
			'std' 		=> 'no',
			'type' 		=> 'checkbox',
			'choices'	=> array(
				'no'			=> __('No', 'jigoshop'),
				'yes'			=> __('Yes', 'jigoshop')
			)
		);

		self::$default_options[] = array(
			'name'		=> __('Crop Thumbnail images','jigoshop'),
			'desc' 		=> '',
			'tip' 		=> __('Use No to set the image size by resizing the image proportionally (that is, without distorting it).<br />Use Yes to set the image size by hard cropping the image (either from the sides, or from the top and bottom).','jigoshop'),
			'id' 		=> 'jigoshop_use_wordpress_thumbnail_crop',
			'std' 		=> 'no',
			'type' 		=> 'checkbox',
			'choices'	=> array(
				'no'			=> __('No', 'jigoshop'),
				'yes'			=> __('Yes', 'jigoshop')
			)
		);

		self::$default_options[] = array(
			'name'		=> __('Crop Catalog images','jigoshop'),
			'desc' 		=> '',
			'tip' 		=> __('Use No to set the image size by resizing the image proportionally (that is, without distorting it).<br />Use Yes to set the image size by hard cropping the image (either from the sides, or from the top and bottom).','jigoshop'),
			'id' 		=> 'jigoshop_use_wordpress_catalog_crop',
			'std' 		=> 'no',
			'type' 		=> 'checkbox',
			'choices'	=> array(
				'no'			=> __('No', 'jigoshop'),
				'yes'			=> __('Yes', 'jigoshop')
			)
		);

		self::$default_options[] = array(
			'name'		=> __('Crop Large images','jigoshop'),
			'desc' 		=> '',
			'tip' 		=> __('Use No to set the image size by resizing the image proportionally (that is, without distorting it).<br />Use Yes to set the image size by hard cropping the image (either from the sides, or from the top and bottom).','jigoshop'),
			'id' 		=> 'jigoshop_use_wordpress_featured_crop',
			'std' 		=> 'no',
			'type' 		=> 'checkbox',
			'choices'	=> array(
				'no'			=> __('No', 'jigoshop'),
				'yes'			=> __('Yes', 'jigoshop')
			)
		);

		self::$default_options[] = array( 'name' => __('Image Sizes', 'jigoshop'), 'type' => 'title', 'desc' => '' );

		self::$default_options[] = array(
			'name' 		=> __('Tiny Image Width','jigoshop'),
			'desc' 		=> __('Default = 36px','jigoshop'),
			'tip' 		=> __('Set the width of the small image used in the Cart, Checkout, Orders and Widgets.','jigoshop'),
			'id' 		=> 'jigoshop_shop_tiny_w',
			'type' 		=> 'natural',
			'std' 		=> 36
		);

		self::$default_options[] = array(
			'name' 		=> __('Tiny Image Height','jigoshop'),
			'desc' 		=> __('Default = 36px','jigoshop'),
			'tip' 		=> __('Set the height of the small image used in the Cart, Checkout, Orders and Widgets.','jigoshop'),
			'id' 		=> 'jigoshop_shop_tiny_h',
			'type' 		=> 'natural',
			'std' 		=> 36
		);

		self::$default_options[] = array(
			'name' 		=> __('Thumbnail Image Width','jigoshop'),
			'desc' 		=> __('Default = 90px','jigoshop'),
			'tip' 		=> __('Set the width of the thumbnail image for Single Product page extra images.','jigoshop'),
			'id' 		=> 'jigoshop_shop_thumbnail_w',
			'type' 		=> 'natural',
			'std' 		=> 90
		);

		self::$default_options[] = array(
			'name' 		=> __('Thumbnail Image Height','jigoshop'),
			'desc' 		=> __('Default = 90px','jigoshop'),
			'tip' 		=> __('Set the height of the thumbnail image for Single Product page extra images.','jigoshop'),
			'id' 		=> 'jigoshop_shop_thumbnail_h',
			'type' 		=> 'natural',
			'std' 		=> 90
		);

		self::$default_options[] = array(
			'name' 		=> __('Catalog Image Width','jigoshop'),
			'desc' 		=> __('Default = 150px','jigoshop'),
			'tip' 		=> __('Set the width of the catalog image for Shop, Categories, Tags, and Related Products.','jigoshop'),
			'id' 		=> 'jigoshop_shop_small_w',
			'type' 		=> 'natural',
			'std' 		=> 150
		);

		self::$default_options[] = array(
			'name' 		=> __('Catalog Image Height','jigoshop'),
			'desc' 		=> __('Default = 150px','jigoshop'),
			'tip' 		=> __('Set the height of the catalog image for Shop, Categories, Tags, and Related Products.','jigoshop'),
			'id' 		=> 'jigoshop_shop_small_h',
			'type' 		=> 'natural',
			'std' 		=> 150
		);

		self::$default_options[] = array(
			'name' 		=> __('Large Image Width','jigoshop'),
			'desc' 		=> __('Default = 300px','jigoshop'),
			'tip' 		=> __('Set the width of the Single Product page large or Featured image.','jigoshop'),
			'id' 		=> 'jigoshop_shop_large_w',
			'type' 		=> 'natural',
			'std' 		=> 300
		);

		self::$default_options[] = array(
			'name' 		=> __('Large Image Height','jigoshop'),
			'desc' 		=> __('Default = 300px','jigoshop'),
			'tip' 		=> __('Set the height of the Single Product page large or Featured image.','jigoshop'),
			'id' 		=> 'jigoshop_shop_large_h',
			'type' 		=> 'natural',
			'std' 		=> 300
		);

		/**
		 * Products & Inventory Tab
		 *------------------------------------------------------------------------------------------
		*/
		self::$default_options[] = array( 'type' => 'tab', 'name' => __('Products &amp; Inventory', 'jigoshop') );

		self::$default_options[] = array( 'name' => __('Product Options', 'jigoshop'), 'type' => 'title', 'desc' => '' );

		self::$default_options[] = array(
			'name'		=> __('Enable SKU field','jigoshop'),
			'desc' 		=> '',
			'tip' 		=> __('Turning off the SKU field will give products an SKU of their post id.','jigoshop'),
			'id' 		=> 'jigoshop_enable_sku',
			'std' 		=> 'no',
			'type' 		=> 'checkbox',
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
			'type' 		=> 'checkbox',
			'choices'	=> array(
				'no'			=> __('No', 'jigoshop'),
				'yes'			=> __('Yes', 'jigoshop')
			)
		);

		self::$default_options[] = array(
			'name'		=> __('Weight Unit', 'jigoshop'),
			'desc' 		=> '',
			'tip' 		=> __("This controls what unit you will define weights in.", 'jigoshop'),
			'id' 		=> 'jigoshop_weight_unit',
			'std' 		=> 'kg',
			'type' 		=> 'radio',
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
			'type' 		=> 'checkbox',
			'choices'	=> array(
				'no'			=> __('No', 'jigoshop'),
				'yes'			=> __('Yes', 'jigoshop')
			)
		);

		self::$default_options[] = array(
			'name'		=> __('Dimensions Unit', 'jigoshop'),
			'desc' 		=> '',
			'tip' 		=> __("This controls what unit you will define dimensions in.", 'jigoshop'),
			'id' 		=> 'jigoshop_dimension_unit',
			'std' 		=> 'cm',
			'type' 		=> 'radio',
			'choices'	=> array(
				'cm'			=> __('centimeters', 'jigoshop'),
				'in'			=> __('inches', 'jigoshop')
			)
		);

		self::$default_options[] = array(
			'name'		=> __('Product thumbnail images per row','jigoshop'),
			'desc' 		=> __('Default = 3','jigoshop'),
			'tip' 		=> __('Determines how many extra product thumbnail images attached to a product to show on one row for the Single Product page.','jigoshop'),
			'id' 		=> 'jigoshop_product_thumbnail_columns',
			'std' 		=> '3',
			'type' 		=> 'range',
			'extra'		=> array(
				'min'			=> 1,
				'max'			=> 10,
				'step'			=> 1
			)
		);

		self::$default_options[] = array(
			'name'		=> __('Show related products','jigoshop'),
			'desc' 		=> '',
			'tip' 		=> __('To show or hide the related products section on a single product page.','jigoshop'),
			'id' 		=> 'jigoshop_enable_related_products',
			'std' 		=> 'yes',
			'type' 		=> 'checkbox',
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
			'type' 		=> 'checkbox',
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
			'type' 		=> 'checkbox',
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
			'type' 		=> 'checkbox',
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
			'type' 		=> 'natural',
			'std' 		=> '2'
		);

		self::$default_options[] = array(
			'name'		=> __('Notify on out of stock','jigoshop'),
			'desc' 		=> '',
			'id' 		=> 'jigoshop_notify_no_stock',
			'std' 		=> 'yes',
			'type' 		=> 'checkbox',
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
			'type' 		=> 'natural',
			'std' 		=> '0'
		);

		self::$default_options[] = array(
			'name'		=> __('Hide out of stock products','jigoshop'),
			'desc' 		=> '',
			'tip' 		=> __('For Yes: When the Out of Stock Threshold (above) is reached, the product visibility will be set to hidden so that it will not appear on the Catalog or Shop product lists.','jigoshop'),
			'id' 		=> 'jigoshop_hide_no_stock_product',
			'std' 		=> 'no',
			'type' 		=> 'checkbox',
			'choices'	=> array(
				'no'			=> __('No', 'jigoshop'),
				'yes'			=> __('Yes', 'jigoshop')
			)
		);

		/**
		 * Tax Tab
		 *------------------------------------------------------------------------------------------
		*/
		self::$default_options[] = array( 'type' => 'tab', 'name' => __('Tax', 'jigoshop') );

		self::$default_options[] = array( 'name' => __('Tax Options', 'jigoshop'), 'type' => 'title', 'desc' => '' );

		self::$default_options[] = array(
			'name'		=> __('Calculate Taxes','jigoshop'),
			'desc' 		=> __('Only turn this off if you are exclusively selling non-taxable items.','jigoshop'),
			'tip' 		=> __('If you are not calculating taxes then you can ignore all other tax options.', 'jigoshop'),
			'id' 		=> 'jigoshop_calc_taxes',
			'std' 		=> 'yes',
			'type' 		=> 'checkbox',
			'choices'	=> array(
				'no'			=> __('No', 'jigoshop'),
				'yes'			=> __('Yes', 'jigoshop')
			)
		);

		self::$default_options[] = array(
			'name' 		=> __('Apply Taxes After Coupon','jigoshop'),
			'desc' 		=> __('This will have no effect if Calculate Taxes is turned off.','jigoshop'),
			'tip' 		=> __('If yes, taxes get applied after coupons. When no, taxes get applied before coupons.','jigoshop'),
			'id' 		=> 'jigoshop_tax_after_coupon',
			'std' 		=> 'yes',
			'type' 		=> 'checkbox',
			'choices'	=> array(
				'no'			=> __('No', 'jigoshop'),
				'yes'			=> __('Yes', 'jigoshop')
			)
		);

		self::$default_options[] = array(
			'name'		=> __('Catalog Prices include tax?','jigoshop'),
			'desc' 		=> __('This will only apply to the Shop, Category and Product pages.','jigoshop'),
			'tip' 		=> __('This will have no effect on the Cart, Checkout, Emails, or final Orders; prices are always shown with tax out.','jigoshop'),
			'id' 		=> 'jigoshop_prices_include_tax',
			'std' 		=> 'no',
			'type' 		=> 'checkbox',
			'choices'	=> array(
				'no'			=> __('No', 'jigoshop'),
				'yes'			=> __('Yes', 'jigoshop')
			)
		);

		self::$default_options[] = array(
			'name'		=> __('Additional Tax classes','jigoshop'),
			'desc' 		=> __('List 1 per line. This is in addition to the default <em>Standard Rate</em>.','jigoshop'),
			'tip' 		=> __('List product and shipping tax classes here, e.g. Zero Tax, Reduced Rate.','jigoshop'),
			'id' 		=> 'jigoshop_tax_classes',
			'type' 		=> 'textarea',
			'std' 		=> sprintf( __( 'Reduced Rate%sZero Rate', 'jigoshop' ), PHP_EOL )
		);

		self::$default_options[] = array(
			'name'		=> __('Tax rates','jigoshop'),
			'desc' 		=> '',
			'tip' 		=> __('To avoid rounding errors, insert tax rates with 4 decimal places.','jigoshop'),
			'id' 		=> 'jigoshop_tax_rates',
			'type' 		=> 'tax_rates',
			'std' 		=> array()
		);

		/**
		 * Shipping Tab
		 *------------------------------------------------------------------------------------------
		*/
		self::$default_options[] = array( 'type' => 'tab', 'name' => __('Shipping', 'jigoshop') );

		self::$default_options[] = array( 'name' => __('Shipping Options', 'jigoshop'), 'type' => 'title', 'desc' => '' );

		self::$default_options[] = array(
			'name'		=> __('Enable Shipping','jigoshop'),
			'desc' 		=> __('Only turn this off if you are <strong>not</strong> shipping items, or items have shipping costs included.','jigoshop'),
			'tip' 		=> __('If turned off, this will also remove shipping address fields on the Checkout.','jigoshop'),
			'id' 		=> 'jigoshop_calc_shipping',
			'std' 		=> 'yes',
			'type' 		=> 'checkbox',
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
			'type' 		=> 'checkbox',
			'choices'	=> array(
				'no'			=> __('No', 'jigoshop'),
				'yes'			=> __('Yes', 'jigoshop')
			)
		);

		self::$default_options[] = array(
			'name'		=> __('Only ship to billing address?','jigoshop'),
			'desc' 		=> '',
			'tip' 		=> __('When activated, Shipping address fields will not appear on the Checkout.','jigoshop'),
			'id' 		=> 'jigoshop_ship_to_billing_address_only',
			'std' 		=> 'no',
			'type' 		=> 'checkbox',
			'choices'	=> array(
				'no'			=> __('No', 'jigoshop'),
				'yes'			=> __('Yes', 'jigoshop')
			)
		);

		self::$default_options[] = array(
			'name'		=> __('Checkout always shows Shipping fields?','jigoshop'),
			'desc' 		=> __('This will have no effect if "Only ship to billing address" is activated.','jigoshop'),
			'tip' 		=> __('When activated, Shipping address fields will appear by default on the Checkout.','jigoshop'),
			'id' 		=> 'jigoshop_show_checkout_shipping_fields',
			'std' 		=> 'no',
			'type' 		=> 'checkbox',
			'choices'	=> array(
				'no'			=> __('No', 'jigoshop'),
				'yes'			=> __('Yes', 'jigoshop')
			)
		);

		self::$default_options[] = array( 'name' => __('Available Shipping Methods', 'jigoshop'), 'type' => 'title', 'desc' => '' );

		self::$default_options[] = array( 'type' => 'shipping_options');  // required only for backwards compatibility.

		/**
		 * Payment Gateways Tab
		 *------------------------------------------------------------------------------------------
		*/
		self::$default_options[] = array( 'type' => 'tab', 'name' => __('Payment Gateways', 'jigoshop') );

		self::$default_options[] = array( 'name' => __('Available gateways', 'jigoshop'), 'type' => 'title', 'desc' => '' );

		self::$default_options[] = array( 'type' => 'gateway_options');  // required only for backwards compatibility.

		/**
		 * Extensions are encouraged to use any of the 'install_external_options' variants here to add options
		 * We will check for the old means of installing options prior to Jigoshop 1.3 and
		 * and display a warning if it is detected from the 'jigoshop_options_settings' filter.
		 *------------------------------------------------------------------------------------------
		*/
		$other_options = apply_filters( 'jigoshop_options_settings', array() );

		if ( ! empty( $other_options )) foreach ( $other_options as $index => $option ) {
			switch ( $option['type'] ) {
			case 'tab':
				$this->bad_extensions[] = $option['tabname'];
				add_action( 'admin_notices', array ($this, 'jigoshop_deprecated_options') );
				break;
			case 'title':
				$this->bad_extensions[] = $option['name'];
				add_action( 'admin_notices', array ($this, 'jigoshop_deprecated_options') );
				break;
			}
		}

	}

	public function jigoshop_deprecated_options() {
		echo '<div class="error"><p>' . sprintf( __('The following items, from one or more extensions, have tried to add Jigoshop Settings in a manner that is no longer supported as of Jigoshop 1.3. (%s)', 'jigoshop') . '</p></div>', implode( ', ', $this->bad_extensions ));
	}

}


?>
