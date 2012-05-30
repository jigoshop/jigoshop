<?php
/**
 * Jigoshop_Admin_Settings class for management and display of all Jigoshop option settings
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

class Jigoshop_Admin_Settings extends Jigoshop_Singleton {

	private $our_parser;
    private $jigoshop_options;
	
	
	/**
	 * Constructor
	 *
	 * @since 1.2
	 */
	protected function __construct() {
        $this->jigoshop_options = self::get_jigoshop_options();
		
		$this->our_parser = new Jigoshop_Options_Parser( 
			$this->jigoshop_options->get_default_options(), 
			JIGOSHOP_OPTIONS
		);

		add_action( 'admin_init', array( &$this, 'register_settings' ) );

	}
	
	
	/**
	 * Add options page
	 *
	 * @deprecated -- no longer used; is set up in 'jigoshop-admin.php' function 'jigoshop_admin_menu'.
	 */
	public function add_settings_page() {

		$admin_page = add_submenu_page( 'jigoshop', __( 'Settings' ), __( 'Settings' ), 'manage_options', JIGOSHOP_OPTIONS, array( &$this, 'output_markup' ) );

		add_action( 'admin_print_scripts-' . $admin_page, array( &$this, 'settings_scripts' ) );
		add_action( 'admin_print_styles-' . $admin_page, array( &$this, 'settings_styles' ) );

	}
	
	
	/**
	 * Scripts for the Options page
	 *
	 * @since 1.2
	 */
	public function settings_scripts() {
		
    	wp_register_script( 'jigoshop-easytooltip', jigoshop::assets_url() . '/assets/js/easyTooltip.js', '' );
    	wp_enqueue_script( 'jigoshop-easytooltip' );
    	// http://jquerytools.org/documentation/rangeinput/index.html
    	wp_register_script( 'jquery-tools-slider', 'http://cdn.jquerytools.org/1.2.6/form/jquery.tools.min.js', array( 'jquery' ), '1.2.6' );
    	wp_enqueue_script( 'jquery-tools-slider' );

	}
	
	
	/**
	 * Styling for the options page
	 *
	 * @since 1.2
	 */
	public function settings_styles() {
		do_action( 'jigoshop_settings_styles' );	// user defined stylesheets should be registered and queued
	}
	
	
	/**
	 * Register settings
	 *
	 * @since 1.2
	 */
	public function register_settings() {

		register_setting( JIGOSHOP_OPTIONS, JIGOSHOP_OPTIONS, array ( &$this, 'validate_settings' ) );
		
		$slug = $this->get_current_tab_slug();
		$options = $this->our_parser->tabs[$slug];
		
		foreach ( $options as $index => $option ) {
			switch ( $option['type'] ) {
			case 'title':
				add_settings_section( $option['section'], $option['name'], array( &$this, 'display_section' ), JIGOSHOP_OPTIONS );
				break;
				
			default:
				$this->create_setting( $index, $option );
				break;
			}
		}
		
	}
	
	
	/**
	 * Create a settings field
	 *
	 * @since 1.2
	 */
	public function create_setting( $index, $option = array() ) {
	
		$defaults = array(
			'tab'			=> '',
			'section'		=> '',
			'id'			=> null,
			'type'			=> '',
			'name'			=> __( '' ),
			'desc'			=> __( '' ),
			'tip'			=> '',
			'std'			=> '',
			'choices'		=> array(),
			'class'			=> '',
			'display'		=> null,
			'update'		=> null,
			'extra'			=> null
		);

		extract( wp_parse_args( $option, $defaults ) );
		$id = ! empty( $id ) ? $id : $section.$index;
		
		$field_args = array(
			'tab'			=> $tab,
			'section'		=> $section,
			'id'			=> $id,
			'type'			=> $type,
			'name'			=> $name,
			'desc'			=> $desc,
			'tip'			=> $tip,
			"std"			=> $std,
			'choices'		=> $choices,
			'label_for'		=> $id,
			'class'			=> $class,
			'display'		=> $display,
			'update'		=> $update,
			'extra'			=> $extra
		);
		
		if ( $type <> 'heading' ) {
			add_settings_field( 
				$id, 
				esc_attr( $name ), 
				array( &$this, 'display_option' ), 
				JIGOSHOP_OPTIONS, 
				$section, 
				$field_args
			);
		}
	}
	
	
	/**
	 * Format markup for an option and output it
	 *
	 * @since 1.2
	 */
	public function display_option( $option ) {
		echo $this->our_parser->format_option_for_display( $option );
	}
	
	
	/**
	 * Description for section
	 *
	 * @since 1.2
	 */
	public function display_section( $section ) {
	
		$options = $this->our_parser->these_options;
		foreach ( $options as $index => $option ) {
			if ( isset( $option['name'] ) && $section['title'] == $option['name'] ) {
				if ( ! empty( $option['desc'] )) {
					echo '<p class="section_description">' . $option['desc'] . '</p>';
				}
			}
		}
		
	}
	
	
	/**
	 * Render the Options page
	 *
	 * @since 1.2
	 */
	public function output_markup() {
		?>
			<div class="wrap jigoshop">
			
				<div class="icon32 icon32-jigoshop-settings" id="icon-jigoshop"><br></div>
				<h2 class="nav-tab-wrapper jigoshop-nav-tab-wrapper">
					<?php echo $this->build_tab_menu_items(); ?>
				</h2>
				
				<noscript>
					<div id="jigoshop-js-warning" class="error"><?php _e( 'Warning- This options panel may not work properly without javascript!', 'jigoshop' ); ?></div>
				</noscript>
				
				<?php settings_errors(); ?>
				
				<form action="options.php" method="post">

					<div class="jigoshop-settings">
						<div id="tabs-wrap">
							
							<?php settings_fields( JIGOSHOP_OPTIONS ); ?>
							<?php do_settings_sections( JIGOSHOP_OPTIONS ); ?>
							
							<?php $tabname = $this->get_current_tab_name(); ?>
							<?php $this->jigoshop_options->set_option( 'jigoshop_settings_current_tabname', $tabname ); ?>
							
							<p class="submit"><input name="Submit" type="submit" class="button-primary" value="<?php echo sprintf( __( "Save %s Changes", 'jigoshop' ), $tabname ); ?>" /></p>
						
						</div>
					</div>
					
				</form>

			</div>

			<script type="text/javascript">
				/*<![CDATA[*/
				jQuery(function($) {
					
					// Fade out the status message
					jQuery('.updated').delay(2500).fadeOut(1500);

					// jQuery Tools range tool
					jQuery(":range").rangeinput();
			
					// Countries
					jQuery('select#jigoshop_allowed_countries').change(function(){
						// hide-show multi_select_countries
						if (jQuery(this).val()=="specific") {
							jQuery(this).parent().parent().next('tr').show();
						} else {
							jQuery(this).parent().parent().next('tr').hide();
						}
					}).change();
	
					// permalink double save hack (do we need this anymore -JAP-)
					jQuery.get('<?php echo admin_url('options-permalink.php') ?>');
		
				});
				/*]]>*/
			</script>
			
			<?php do_action( 'jigoshop_settings_scripts' ); ?>
			
		<?php
	}
	
	
	/**
	 * Create the Navigation Menu Tabs
	 *
	 * @since 1.2
	 */
	function build_tab_menu_items() {
		$menus_li = '';
		$slug = $this->get_current_tab_slug();
		foreach ( $this->our_parser->tab_headers as $tab ) {
			$this_slug = sanitize_title( $tab );
			if ( $slug == $this_slug ) {
				$menus_li .= '<a class="nav-tab nav-tab-active" 
					title="'.$tab.'"
					href="?page='.JIGOSHOP_OPTIONS.'&tab='.$this_slug.'">' . $tab . '</a>';
			} else {
				$menus_li .= '<a class="nav-tab"
					title="'.$tab.'"
					href="?page='.JIGOSHOP_OPTIONS.'&tab='.$this_slug.'">' . $tab . '</a>';
			}
		}
		return $menus_li;
	}
	
	
	/**
	 * Return the current Tab slug in view
	 *
	 * @since 1.2
	 */
	public function get_current_tab_slug() {
		$current = "";

		if ( isset( $_GET['tab'] ) ):
			$current = $_GET['tab'];
		else:
			$current = sanitize_title( $this->our_parser->these_options[0]['name'] );
		endif;
		return $current;
	}
	
	
	/**
	 * Return the current Tab full name in view
	 *
	 * @since 1.2
	 */
	public function get_current_tab_name() {
		$current = "";

		if ( isset( $_GET['tab'] ) ) {
			foreach ( $this->our_parser->these_options as $option ) {
				if ( $option['type'] == 'heading' && sanitize_title( $option['name'] ) == $_GET['tab'] ) {
					$current = $option['name'];
				}
			}
		} else {
			$current = $this->our_parser->these_options[0]['name'];
		}
		return $current;
	}
	
	
	/**
	 * Validate settings
	 *
	 * @since 1.2
	 */
	public function validate_settings( $input ) {

		if ( empty( $_POST ) ) {
//			logme( "NO POST is SET in validate" );  // need to check into this
			return $input;
		}
		
		$defaults = $this->our_parser->these_options;
		$current_options = $this->jigoshop_options->get_current_options();
		
		$valid_input = $current_options;			// we start with the current options

			
		// Find the current TAB we are working with and use it's option settings
		$this_section = sanitize_title( $this->jigoshop_options->get_option( 'jigoshop_settings_current_tabname_new' ) );
		$tab = $this->our_parser->tabs[$this_section];
		
		
		// with each option, get it's type and validate it
		foreach ( $tab as $index => $setting ) {
			if ( isset( $setting['id'] ) ) {
				foreach ( $defaults as $default_index => $option ) {
					if ( in_array( $setting['id'], $option ) ) {
						break;
					}
				}
				$value = isset( $input[$setting['id']] ) ? $input[$setting['id']] : null ;
				
				// we have a $setting
				// $value has the WordPress user submitted value for this $setting
				// $option has this $setting parameters
				// validate for $option 'type' checking for a submitted $value
				switch ( $option['type'] ) {
				case 'user_defined' :
					if ( isset( $option['update'] ) ) {
						if ( is_callable( $option['update'], true ) ) {
							$valid_input[$setting['id']] = call_user_func( $option['update'] );
						}
					}
					break;
					
				case 'multi_select_countries' :
					if ( isset( $value ) ) {
						$countries = jigoshop_countries::$countries;
						asort( $countries );
						$selected = array();
						foreach ( $countries as $key => $val ) {
							if ( array_key_exists( $key, (array)$value ) ) {
								$selected[] = $key;
							}
						}
						$valid_input[$setting['id']] = $selected;
					}
					break;
					
				case 'checkbox' :
					// there will be no $value for a false checkbox, set it now
					$valid_input[$setting['id']] = isset( $value ) ? 'yes' : 'no';
					break;
					
				case 'multicheck' :
					$selected = array();
					foreach ( $option['choices'] as $key => $val ) {
						if ( isset( $value[$key] ) ) {
							$selected[$key] = true;
						} else {
							$selected[$key] = false;
						}
					}
					$valid_input[$setting['id']] = $selected;
					break;
					
				case 'text' :
				case 'longtext' :
				case 'textarea' :
					$valid_input[$setting['id']] = esc_attr( jigowatt_clean( $value ) );
					break;
					
				case 'email' :
					$email = sanitize_email( $value );
					if ( $email <> $value ) {
						add_settings_error( 
							$setting['id'],
							'jigoshop_email_error',
							sprintf(__('You entered "%s" as the value for "%s" and it was not a valid email address.  It was not saved and the original is still in use.','jigoshop'), $value, $setting['name']),
							'error'
						);
						$valid_input[$setting['id']] = $current_options[$setting['id']];
					} else
						$valid_input[$setting['id']] = esc_attr( jigowatt_clean( $email ) );
					break;
					
				case 'decimal' :
					$cleaned = jigowatt_clean( $value );
					if ( ! jigoshop_validation::is_decimal( $cleaned ) ) {
						add_settings_error( 
							$setting['id'],
							'jigoshop_decimal_error',
							sprintf(__('You entered "%s" as the value for "%s" and it was not a valid decimal number (may have leading negative sign, with optional decimal point, numbers 0-9).  It was not saved and the original is still in use.','jigoshop'), $value, $setting['name']),
							'error'
						);
						$valid_input[$setting['id']] = $current_options[$setting['id']];
					} else
						$valid_input[$setting['id']] = $cleaned;
					break;
					
				case 'integer' :
					$cleaned = jigowatt_clean( $value );
					if ( ! jigoshop_validation::is_integer( $cleaned ) ) {
						add_settings_error( 
							$setting['id'],
							'jigoshop_integer_error',
							sprintf(__('You entered "%s" as the value for "%s" and it was not a valid integer number (may have leading negative sign, numbers 0-9).  It was not saved and the original is still in use.','jigoshop'), $value, $setting['name']),
							'error'
						);
						$valid_input[$setting['id']] = $current_options[$setting['id']];
					} else
						$valid_input[$setting['id']] = $cleaned;
					break;
					
				case 'natural' :
					$cleaned = jigowatt_clean( $value );
					if ( ! jigoshop_validation::is_natural( $cleaned ) ) {
						add_settings_error( 
							$setting['id'],
							'jigoshop_natural_error',
							sprintf(__('You entered "%s" as the value for "%s" and it was not a valid natural number (numbers 0-9).  It was not saved and the original is still in use.','jigoshop'), $value, $setting['name']),
							'error'
						);
						$valid_input[$setting['id']] = $current_options[$setting['id']];
					} else
						$valid_input[$setting['id']] = $cleaned;
					break;
					
				default :
					if ( isset( $value ) ) {
						$valid_input[$setting['id']] = $value;
					}
					break;
				}
			}
		}
		
		
		// both coupons and tax classes should be updated
		// they will do nothing if this is not the right TAB
		if ( $result = $this->get_updated_coupons() ) $valid_input['jigoshop_coupons'] = $result;
		if ( $result = $this->get_updated_tax_classes() ) $valid_input['jigoshop_tax_rates'] = $result;
		
		
		// Allow any hooked in option updating
		// TODO: not sure we really need this anymore (-JAP-)
		do_action( 'jigoshop_update_options' );
		
		$errors = get_settings_errors();
		if ( empty( $errors ) ) {
			add_settings_error(
				'',
				'settings_updated',
				sprintf(__('"%s" settings were updated successfully.','jigoshop'), $this->jigoshop_options->get_option( 'jigoshop_settings_current_tabname_new' )),
				'updated'
			);
		}
		
		return $valid_input;	// send it back to WordPress for saving
		
	}
	
	
	/**
	 * When Options are saved, return the 'jigoshop_coupons' option values
	 *
	 * @return	mixed	false if not coupons, array of coupons otherwise
	 *
	 * @since 		1.2
	 */
	function get_updated_coupons() {
		
		// make sure we are on the Coupons Tab
		if ( ! isset( $_POST['coupon_code'] )) return false;
		
		$coupon_code = array();
		$coupon_type = array();
		$coupon_amount = array();
		$product_ids = array();
		$date_from = array();
		$date_to = array();
		$coupons = array();
		$individual = array();

		if ( isset( $_POST['coupon_code'] ))
			$coupon_code = $_POST['coupon_code'];
		if ( isset( $_POST['coupon_type'] ))
			$coupon_type = $_POST['coupon_type'];
		if ( isset( $_POST['coupon_amount'] ))
			$coupon_amount = $_POST['coupon_amount'];
		if ( isset( $_POST['product_ids'] ))
			$product_ids = $_POST['product_ids'];
		if ( isset( $_POST['coupon_date_from'] ))
			$date_from = $_POST['coupon_date_from'];
		if ( isset( $_POST['coupon_date_to'] ))
			$date_to = $_POST['coupon_date_to'];
		if ( isset( $_POST['individual'] ))
			$individual = $_POST['individual'];

		for ( $i = 0; $i < sizeof( $coupon_code ); $i++ ) :

			if ( isset( $coupon_code[$i] )
				&& isset( $coupon_type[$i] )
				&& isset( $coupon_amount[$i] )) :

				$code = jigowatt_clean( $coupon_code[$i] );
				$type = jigowatt_clean( $coupon_type[$i] );
				$amount = jigowatt_clean( $coupon_amount[$i] );

				if ( isset( $product_ids[$i] ) && $product_ids[$i] ) :
					$products = array_map( 'trim', explode( ',', $product_ids[$i] ));
				else :
					$products = array();
				endif;
				
				if ( isset( $date_from[$i] ) && $date_from[$i] ) :
					$from_date = strtotime( $date_from[$i] );
				else :
					$from_date = 0;
				endif;
				
				if ( isset( $date_to[$i] ) && $date_to[$i] ) :
					$to_date = strtotime( $date_to[$i] ) + (60 * 60 * 24 - 1);
				else :
					$to_date = 0;
				endif;

				if ( isset( $individual[$i] ) && $individual[$i] ) :
					$individual_use = 'yes';
				else :
					$individual_use = 'no';
				endif;

				if ( $code && $type && $amount ) :
					$coupons[$code] = array(
						'code' => $code,
						'amount' => $amount,
						'type' => $type,
						'products' => $products,
						'date_from' => $from_date,
						'date_to' => $to_date,
						'individual_use' => $individual_use
					);
				endif;

			endif;

		endfor;
		
		return $coupons;
		
	}
	
	
	/**
	 * Defines a custom sort for the tax_rates array. The sort that is needed is that the array is sorted
	 * by country, followed by state, followed by compound tax. The difference is that compound must be sorted based
	 * on compound = no before compound = yes. Ultimately, the purpose of the sort is to make sure that country, state
	 * are all consecutive in the array, and that within those groups, compound = 'yes' always appears last. This is
	 * so that tax classes that are compounded will be executed last in comparison to those that aren't.
	 * last.
	 * <br>
	 * <pre>
	 * eg. country = 'CA', state = 'QC', compound = 'yes'<br>
	 *     country = 'CA', state = 'QC', compound = 'no'<br>
	 *
	 * will be sorted to have <br>
	 *     country = 'CA', state = 'QC', compound = 'no'<br>
	 *     country = 'CA', state = 'QC', compound = 'yes' <br>
	 * </pre>
	 *
	 * @param type $a the first object to compare with (our inner array)
	 * @param type $b the second object to compare with (our inner array)
	 * @return int the results of strcmp
	 */
	function csort_tax_rates($a, $b) {
		$str1 = '';
		$str2 = '';
	
		$str1 .= $a['country'] . $a['state'] . ($a['compound'] == 'no' ? 'a' : 'b');
		$str2 .= $b['country'] . $b['state'] . ($b['compound'] == 'no' ? 'a' : 'b');
	
		return strcmp($str1, $str2);
	}
	
	
	/**
	 * When Options are saved, return the 'jigoshop_tax_rates' option values
	 *
	 * @return	mixed	false if not rax rates, array of tax rates otherwise
	 *
	 * @since 		1.2
	 */
	function get_updated_tax_classes() {
		
		// make sure we are on the Tax Tab
		if ( ! isset( $_POST['tax_classes'] )) return false;
		
			$tax_classes = array();
			$tax_countries = array();
			$tax_rate = array();
			$tax_label = array();
			$tax_rates = array();
			$tax_shipping = array();
			$tax_compound = array();

			if ( isset( $_POST['tax_classes'] )) :
				$tax_classes = $_POST['tax_classes'];
			endif;
			if ( isset( $_POST['tax_country'] )) :
				$tax_countries = $_POST['tax_country'];
			endif;
			if ( isset( $_POST['tax_rate'] )) :
				$tax_rate = $_POST['tax_rate'];
			endif;
			if ( isset( $_POST['tax_label'] )) :
				$tax_label = $_POST['tax_label'];
			endif;
			if ( isset( $_POST['tax_shipping'] )) :
				$tax_shipping = $_POST['tax_shipping'];
			endif;
			if ( isset( $_POST['tax_compound'] )) :
				$tax_compound = $_POST['tax_compound'];
			endif;

			for ( $i = 0; $i < sizeof( $tax_classes ); $i++ ) :

				if ( isset( $tax_classes[$i] ) && isset( $tax_countries[$i] ) && isset( $tax_rate[$i] ) && $tax_rate[$i] && is_numeric( $tax_rate[$i] )) :

					$country = jigowatt_clean($tax_countries[$i]);
					$label = trim($tax_label[$i]);
					$state = '*'; // countries with no states have to have a character for products. Therefore use *
					$rate = number_format((float)jigowatt_clean($tax_rate[$i]), 4);
					$class = jigowatt_clean($tax_classes[$i]);

					if (isset($tax_shipping[$i]) && $tax_shipping[$i])
						$shipping = 'yes'; else
						$shipping = 'no';
					if (isset($tax_compound[$i]) && $tax_compound[$i])
						$compound = 'yes'; else
						$compound = 'no';

					// Get state from country input if defined
					if (strstr($country, ':')) :
						$cr = explode(':', $country);
						$country = current($cr);
						$state = end($cr);
					endif;

					if ($state == '*' && jigoshop_countries::country_has_states($country)) : // handle all-states

						foreach (array_keys(jigoshop_countries::$states[$country]) as $st) :
							$tax_rates[] = array(
								'country' => $country,
								'label' => $label,
								'state' => $st,
								'rate' => $rate,
								'shipping' => $shipping,
								'class' => $class,
								'compound' => $compound,
								'is_all_states' => true //determines if admin panel should show 'all_states'
							);
						endforeach;

					else :

						 $tax_rates[] = array(
							'country' => $country,
							'label' => $label,
							'state' => $state,
							'rate' => $rate,
							'shipping' => $shipping,
							'class' => $class,
							'compound' => $compound,
							'is_all_states' => false //determines if admin panel should show 'all_states'
						);
					endif;


				endif;

			endfor;


		// apply a custom sort to the tax_rates array.
		usort( $tax_rates, array( &$this, 'csort_tax_rates' ));
		
		return $tax_rates;
		
	}
		
}


/**
 * Options Parser Class
 *
 * Used by the Jigoshop_Admin_Settings class to parse the Jigoshop_Options into sections
 * Provides formatted output for display of all Option types
 *
 * @since 		1.2
 */
class Jigoshop_Options_Parser {

	var $these_options;		// The array of default options items to parse
	var $tab_headers;
	var $tabs;
	var $sections;


	function __construct( $option_items, $this_options_entry ) {
		$this->these_options = $option_items;
		$this->parse_options();
	}


	private function parse_options() {
		
		$tab_headers = array();
		$tabs = array();
		$sections = array();
		
		foreach ( $this->these_options as $item ) {
			
			$defaults = array(
				'tab'			=> '',
				'section'		=> '',
				'id'			=> null,
				'type'			=> '',
				'name'			=> __( '' ),
				'desc'			=> __( '' ),
				'tip'			=> '',
				'std'			=> '',
				'choices'		=> array(),
				'class'			=> '',
				'display'		=> null,
				'update'		=> null,
				'extra'			=> null
			);
	
			$item = wp_parse_args( $item, $defaults );
			
			if ( isset( $item['id'] ) ) $item['id'] = sanitize_title( $item['id'] );
			
			if ( $item['type'] == "heading" ) {
				$tab_name = sanitize_title( $item['name'] );
				$tab_headers[$tab_name] = $item['name'];
				continue;
			}
						
			if ( $item['type'] == "title" ) {
				$section_name = sanitize_title( $item['name'] );
			}
			
			$item['tab'] = $tab_name;
			$item['section'] = isset( $section_name ) ? $section_name : $tab_name;
			$tabs[$tab_name][] = $item;
			$sections[$item['section']][] = $item;
			
		}

		$this->tab_headers = $tab_headers;
		$this->tabs = $tabs;
		$this->sections = $sections;
	}
	
	
	public function format_option_for_display( $item ) {
	
		$data = $this->jigoshop_options->get_current_options();
		
		$display = "";					// each item builds it's output into this and it's returned for echoing
		$class = "";
		
		if ( isset( $item['class'] ) ) {
			$class = $item['class'];
		}
		
		// display a tooltip if there is one in it's own table data element before the item to display
		$display .= '<td class="jigoshop-tooltips">';
        if ( ! empty( $item['tip'] )) {
			$display .= '<a href="#" tip="'.esc_attr( $item['tip'] ).'" class="tips" tabindex="99"></a>';
		}
		$display .= '</td>';
		
		$display .= '<td class="forminp">';
		
		// work off the option type and format output for display for each type
		switch ( $item['type'] ) {
		case 'user_defined' :
			if ( isset( $item['display'] ) ) {
				if ( is_callable( $item['display'], true ) ) {
					$display .= call_user_func( $item['display'] );
				}
			}
			break;
			
		case 'gateway_options' :
// 			foreach ( jigoshop_payment_gateways::payment_gateways() as $gateway ) :
// 				$gateway->admin_options();
// 			endforeach;
			break;
			
		case 'shipping_options' :
 			foreach ( jigoshop_shipping::get_all_methods() as $shipping_method ) :
 				$shipping_method->admin_options();
 			endforeach;
			break;
			
		case 'tax_rates' :
			$display .= $this->format_tax_rates_for_display( $item );
			break;
			
		case 'coupons' :
			$display .= $this->format_coupons_for_display( $item );
			break;
			
/*		case 'image_size' :			// may not use this, needs work, unhooking (-JAP-)
			$width = $data[$item['id']];
			$display .= '<input
				name="'.JIGOSHOP_OPTIONS.'['.$item['id'].']"
				id="'.JIGOSHOP_OPTIONS.'['.$item['id'].']"
				class="jigoshop-input jigoshop-text"
				type="text"
				value="'.esc_attr( $data[$item['id']] ).'" />';
			break;
*/		
		case 'single_select_page' :
			$page_setting = (int) $data[$item['id']];
			$args = array(
				'name' => JIGOSHOP_OPTIONS . '[' . $item['id'] . ']',
				'id' => $item['id'],
				'sort_order' => 'ASC',
				'echo' => 0,
				'selected' => $page_setting
			);
			if ( isset( $item['extra'] )) $args = wp_parse_args( $item['extra'], $args );
			$display .= wp_dropdown_pages( $args );
			$parts = explode( '<select', $display );
			$display = $parts[0] . '<select class="'.$class.'"' . $parts[1];
			break;

		case 'single_select_country' :
			$countries = jigoshop_countries::$countries;
			$country_setting = (string) $data[$item['id']];
			if ( strstr( $country_setting, ':' )) :
				$country = current( explode( ':', $country_setting) );
				$state = end( explode( ':', $country_setting) );
			else :
				$country = $country_setting;
				$state = '*';
			endif;
			$display .= '<select class="single_select_country '.$class.'" name="' . JIGOSHOP_OPTIONS . '[' . $item['id'] . ']">';
			$display .= jigoshop_countries::country_dropdown_options($country, $state, false, true, false);
			$display .= '</select>';
			break;
			
		case 'multi_select_countries' :
			$countries = jigoshop_countries::$countries;
			asort( $countries );
			$selections = (array) $data[$item['id']];
			$display .= '<div class="multi_select_countries '.$class.'"><ul>';
			$index = 0;
			foreach ( $countries as $key => $val ) {
				$display .= '<li><label><input type="checkbox" name="' . JIGOSHOP_OPTIONS . '[' . $item['id'] . ']['.$key.'] value="' . esc_attr( $key ) . '" ';
				$display .= in_array( $key, $selections ) ? 'checked="checked" />' : ' />';
				$display .=  esc_attr( $val ) . '</label></li>';
			}
			$display .= '</ul></div>';
			break;
			
		case 'decimal':				// decimal numbers are positive or negative 0-9 inclusive, may include decimal
		case 'integer':				// integer numbers are positive or negative 0-9 inclusive
		case 'natural':				// natural numbers are positive 0-9 inclusive
		case 'text':				// any character sequence
			$display .= '<input
				id="'.$item['id'].'"
				class="jigoshop-input jigoshop-text '.$class.'"
				name="'.JIGOSHOP_OPTIONS.'['.$item['id'].']"
				type="text"
				size="20"
				value="'. esc_attr( $data[$item['id']] ).'" />';
			break;

		case 'midtext':
			$display .= '<input
				id="'.$item['id'].'"
				class="jigoshop-input jigoshop-text '.$class.'"
				name="'.JIGOSHOP_OPTIONS.'['.$item['id'].']"
				type="text"
				size="40"
				value="'. esc_attr( $data[$item['id']] ).'" />';
			break;

		case 'longtext':
			$display .= '<input
				id="'.$item['id'].'"
				class="jigoshop-input jigoshop-text '.$class.'"
				name="'.JIGOSHOP_OPTIONS.'['.$item['id'].']"
				type="text"
				size="80"
				value="'. esc_attr( $data[$item['id']] ).'" />';
			break;

		case 'email':
			$display .= '<input
				id="'.$item['id'].'"
				class="jigoshop-input jigoshop-text jigoshop-email '.$class.'"
				name="'.JIGOSHOP_OPTIONS.'['.$item['id'].']"
				type="text"
				size="40"
				value="'. esc_attr( $data[$item['id']] ).'" />';
			break;

		case 'textarea':
			$cols = '60';
			$ta_value = '';
			if ( isset( $item['choices'] ) ) {
				$ta_options = $item['choices'];
				if ( isset( $ta_options['cols'] ) ) {
					$cols = $ta_options['cols'];
				}
			}
			$ta_value = stripslashes( $data[$item['id']] );
			$display .= '<textarea
				id="'.$item['id'].'"
				class="jigoshop-input jigoshop-textarea '.$class.'"
				name="'.JIGOSHOP_OPTIONS.'['.$item['id'].']"
				cols="'.$cols.'"
				rows="4">'.esc_textarea( $ta_value ).'</textarea>';
			break;

		case "radio":
			// default to horizontal display of choices ( 'horizontal' may or may not be defined )
			if ( ! isset( $item['extra'] ) || ! in_array( 'vertical', $item['extra'] ) ) {

				$display .= '<div class="jigoshop-radio-horz">';
				foreach ( $item['choices'] as $option => $name ) {
					$display .= '<input
						class="jigoshop-input jigoshop-radio '.$class.'"
						name="'.JIGOSHOP_OPTIONS.'['.$item['id'].']"
						type="radio"
						value="'.$option.'" '.checked( $data[$item['id']], $option, false ).' /><label>'.$name.'</label>';
				}
				$display .= '</div>';
				
			} else if ( isset( $item['extra'] ) && in_array( 'vertical', $item['extra'] ) ) {
			
				$display .= '<ul class="jigoshop-radio-vert">';
				foreach ( $item['choices'] as $option => $name ) {
					$display .= '<li><input
						class="jigoshop-input jigoshop-radio '.$class.'"
						name="'.JIGOSHOP_OPTIONS.'['.$item['id'].']"
						type="radio"
						value="'.$option.'" '.checked( $data[$item['id']], $option, false ).' /><label>'.$name.'</label></li>';
				}
				$display .= '</ul>';
				
			}
			break;

		case 'checkbox':
			$display .= '<input
				id="'.$item['id'].'"
				type="checkbox"
				class="jigoshop-input jigoshop-checkbox '.$class.'"
				name="'.JIGOSHOP_OPTIONS.'['.$item['id'].']"
				'.checked($data[$item['id']], 'yes', false).' />';
			break;

		case 'multicheck':
			$multi_stored = $data[$item['id']];
			
			// default to horizontal display of choices ( 'horizontal' may or may not be defined )
			if ( ! isset( $item['extra'] ) || ! in_array( 'vertical', $item['extra'] ) ) {
			
				$display .= '<div class="jigoshop-multi-checkbox-horz '.$class.'">';
				foreach ( $item['choices'] as $key => $option ) {
					$display .= '<input
						id="'.$item['id'].'_'.$key.'"
						class="jigoshop-input"
						name="'.JIGOSHOP_OPTIONS.'['.$item['id'].']['.$key.']"
						type="checkbox"
						'.checked( $multi_stored[$key], true, false ).' />
						<label for="'.$item['id'].'_'.$key.'">'.$option.'</label>';
				}
				$display .= '</div>';
				
			} else if ( isset( $item['extra'] ) && in_array( 'vertical', $item['extra'] ) ) {
			
				$display .= '<ul class="jigoshop-multi-checkbox-vert '.$class.'">';
				foreach ( $item['choices'] as $key => $option ) {
					$display .= '<li><input
						id="'.$item['id'].'_'.$key.'"
						class="jigoshop-input"
						name="'.JIGOSHOP_OPTIONS.'['.$item['id'].']['.$key.']"
						type="checkbox"
						'.checked( $multi_stored[$key], true, false ).' />
						<label for="'.$item['id'].'_'.$key.'">'.$option.'</label></li>';
				}
				$display .= '</ul>';
			}
			break;

		case 'range':
			$display .= '<input
				id="'.$item['id'].'"
				class="jigoshop-input jigoshop-range '.$class.'"
				name="'.JIGOSHOP_OPTIONS.'['.$item['id'].']"
				type="range"
				min="'.$item['extra']['min'].'"
				max="'.$item['extra']['max'].'"
				step="'.$item['extra']['step'].'"
				value="'.$data[$item['id']].'" />';
			break;

		case 'select':
			$display .= '<select
				id="'.$item['id'].'"
				class="jigoshop-input jigoshop-select '.$class.'"
				name="'.JIGOSHOP_OPTIONS.'['.$item['id'].']" >';
			foreach ( $item['choices'] as $value => $label ) {
				$display .= '<option
					value="'.esc_attr( $value ).'" '.selected( $data[$item['id']], $value, false ).' />'.$label.'
					</option>';
			}
			$display .= '</select>';
			break;
		
		default:
//			logme( "UNKOWN _type_ in parsing" );
//			logme( $item );
		}

		if ( $item['type'] != 'heading' ) {
			if ( empty( $item['desc'] ) ) {
				$explain_value = '';
			} else {
				$explain_value = $item['desc'];
			}
			$display .= '<div class="jigoshop-explain"><small>' . $explain_value . '</small></div>';
			$display .= '</td>';
		}
		
		return $display;
	}
	
	
	/*
	 *	Format coupons array for display
	 */
	function format_coupons_for_display( $value ) {
	
		$coupons = new jigoshop_coupons();
		$coupon_codes = (array) $coupons->get_coupons();
		
		ob_start();
		?>

		<div id="jigoshop_coupons">
			<table class="coupon_rows" cellspacing="0">
				<thead>
					<tr>
						<th><?php _e('Remove', 'jigoshop'); ?></th>
						<th><?php _e('Coupon Code', 'jigoshop'); ?></th>
						<th><?php _e('Type', 'jigoshop'); ?></th>
						<th><?php _e('Amount', 'jigoshop'); ?></th>
						<th><?php _e("ID's", 'jigoshop'); ?></th>
						<th><?php _e('From', 'jigoshop'); ?></th>
						<th><?php _e('To', 'jigoshop'); ?></th>
						<th><?php _e('Alone', 'jigoshop'); ?></th>
					</tr>
				</thead>
				<tfoot>
					<tr>
						<th><?php _e('Remove', 'jigoshop'); ?></th>
						<th><?php _e('Coupon Code', 'jigoshop'); ?></th>
						<th><?php _e('Type', 'jigoshop'); ?></th>
						<th><?php _e('Amount', 'jigoshop'); ?></th>
						<th><?php _e("ID's", 'jigoshop'); ?></th>
						<th><?php _e('From', 'jigoshop'); ?></th>
						<th><?php _e('To', 'jigoshop'); ?></th>
						<th><?php _e('Alone', 'jigoshop'); ?></th>
					</tr>
				</tfoot>
				<tbody>
					<?php
					$i = -1;
					if ( ! empty( $coupon_codes ) ) {
						foreach ( $coupon_codes as $coupon ) : $i++;
							echo '<tr>';
							echo '<td><a href="#" class="remove button" title="' . __('Delete this Coupon', 'jigoshop') . '">&times;</a></td>';
							
							echo '<td><input type="text" value="' . esc_attr( $coupon['code'] ) . '" name="coupon_code[' . esc_attr( $i ) . ']" placeholder="' . __('Coupon Code', 'jigoshop') . '" size="8" /></td>';
							
							echo '<td><select name="coupon_type[' . esc_attr( $i ) . ']">';
							$discount_types = array(
								'fixed_cart' => __('Cart Discount', 'jigoshop'),
								'percent' => __('Cart % Discount', 'jigoshop'),
								'fixed_product' => __('Product Discount', 'jigoshop'),
								'percent_product' => __('Product % Discount', 'jigoshop')
							);
							foreach ( $discount_types as $type => $label ) :
								$selected = ($coupon['type'] == $type) ? 'selected="selected"' : '';
								echo '<option value="' . esc_attr( $type ) . '" ' . $selected . '>' . esc_html( $label ) . '</option>';
							endforeach;
							echo '</select></td>';
							
							echo '<td><input type="text" value="' . esc_attr( $coupon['amount'] ) . '" name="coupon_amount[' . esc_attr( $i ) . ']" placeholder="' . __('Amount', 'jigoshop') . '" size="3" /></td>';
							
							echo '<td><input type="text" value="' . ( ( is_array( $coupon['products'] ) ) ? implode( ', ', $coupon['products'] ) : '' ) . '" name="product_ids[' . esc_attr( $i ) . ']" placeholder="' . __('1, 2, 3,', 'jigoshop') . '" size="8" /></td>';
	
							$date_from = $coupon['date_from'];
							echo '<td><label for="coupon_date_from[' . esc_attr( $i ) . ']"></label><input type="text" class="date-pick" name="coupon_date_from[' . esc_attr( $i ) . ']" id="coupon_date_from[' . esc_attr( $i ) . ']" value="';
							if ($date_from) echo date('Y-m-d', $date_from);
							echo '" placeholder="' . __('yyyy-mm-dd', 'jigoshop') . '" size="7" /></td>';
	
							$date_to = $coupon['date_to'];
							echo '<td><label for="coupon_date_to[' . esc_attr( $i ) . ']"></label><input type="text" class="date-pick" name="coupon_date_to[' . esc_attr( $i ) . ']" id="coupon_date_to[' . esc_attr( $i ) . ']" value="';
							if ( $date_to ) echo date('Y-m-d', $date_to);
							echo '" placeholder="' . __('yyyy-mm-dd', 'jigoshop') . '" size="7" /></td>';
	
							echo '<td><input type="checkbox" name="individual[' . esc_attr( $i ) . ']" ';
							if ( isset( $coupon['individual_use'] ) && $coupon['individual_use'] == 'yes' ) echo 'checked="checked"';
							echo ' /></td>';
							
							echo '</tr>';
							?>
							<script type="text/javascript">
							/* <![CDATA[ */
								jQuery(function() {
									jQuery('.date-pick').datepicker( {dateFormat: 'yy-mm-dd', gotoCurrent: true} );
								});
							/* ]]> */
							</script>
							<?php
						endforeach;
					}
					?>
				</tbody>
			</table>
			<div><a href="#" class="add button"><?php _e('+ Add Coupon', 'jigoshop'); ?></a></div>
		</div>
		
		<script type="text/javascript">
		/* <![CDATA[ */
			jQuery(function() {
				jQuery('#jigoshop_coupons a.add').live('click', function() {
					var size = jQuery('.coupon_rows tbody tr').size();
					jQuery('<tr> \
						<td><a href="#" class="remove button" title="<?php __("Delete this Coupon", "jigoshop"); ?>">&times;</a></td> \
						<td><input type="text" value="" name="coupon_code[' + size + ']" placeholder="<?php _e("Coupon Code", "jigoshop"); ?>" size="8" /></td> \
						<td><select name="coupon_type[' + size + ']"> \
							<option value="fixed_cart"><?php _e("Cart Discount", "jigoshop"); ?></option> \
							<option value="percent"><?php _e("Cart % Discount", "jigoshop"); ?></option> \
							<option value="fixed_product"><?php _e("Product Discount", "jigoshop"); ?></option> \
							<option value="percent_product"><?php _e("Product % Discount", "jigoshop"); ?></option> \
							</select></td> \
						<td><input type="text" value="" name="coupon_amount[' + size + ']" placeholder="<?php _e("Amount", "jigoshop"); ?>" size="3" /></td> \
						<td><input type="text" value="" name="product_ids[' + size + ']" \
							placeholder="<?php _e("1, 2, 3,", "jigoshop"); ?>" size="8" /></td> \
						<td><label for="coupon_date_from[' + size + ']"></label> \
							<input type="text" class="date-pick" name="coupon_date_from[' + size + ']" \
							id="coupon_date_from[' + size + ']" value="" \
							placeholder="<?php _e("yyyy-mm-dd", "jigoshop"); ?>" size="7" /></td> \
						<td><label for="coupon_date_to[' + size + ']"></label> \
							<input type="text" class="date-pick" name="coupon_date_to[' + size + ']" \
							id="coupon_date_to[' + size + ']" value="" \
							placeholder="<?php _e("yyyy-mm-dd", "jigoshop"); ?>" size="7" /></td> \
						<td><input type="checkbox" name="individual[' + size + ']" /></td>'
					).appendTo('#jigoshop_coupons .coupon_rows tbody');

					jQuery(function() {
						jQuery('.date-pick').datepicker( {dateFormat: 'yy-mm-dd', gotoCurrent: true} );

					});

					return false;
				});
				jQuery('#jigoshop_coupons a.remove').live('click', function(){
					var answer = confirm("<?php _e('Delete this coupon?', 'jigoshop'); ?>")
					if (answer) jQuery(this).parent().parent().remove();
					return false;
				});
			});
		/* ]]> */
		</script>
		<?php
		
		$output = ob_get_contents(); ob_end_clean();
		
		return $output;
	}
	
	
	/*
	 *	Format tax rates array for display
	 */
	function format_tax_rates_for_display( $value ) {
	
		$_tax = new jigoshop_tax();
		$tax_classes = $_tax->get_tax_classes();
		$tax_rates = (array) $this->jigoshop_options->get_option( 'jigoshop_tax_rates_new' );
		$applied_all_states = array();
		
		ob_start();
		?>
		<div id="jigoshop_tax_rates">
			<table class="tax_rate_rules" cellspacing="0">
				<thead>
					<tr>
						<th><?php _e('Remove', 'jigoshop'); ?></th>
						<th><?php _e('Tax Classes', 'jigoshop'); ?></th>
						<th><?php _e('Online Label', 'jigoshop'); ?></th>
						<th><?php _e('Country/State', 'jigoshop'); ?></th>
						<th><?php _e("Rate (%)", 'jigoshop'); ?></th>
						<th><?php _e('Apply to shipping', 'jigoshop'); ?></th>
						<th><?php _e('Compound', 'jigoshop'); ?></th>
					</tr>
				</thead>
				<tfoot>
					<tr>
						<th><?php _e('Remove', 'jigoshop'); ?></th>
						<th><?php _e('Tax Classes', 'jigoshop'); ?></th>
						<th><?php _e('Online Label', 'jigoshop'); ?></th>
						<th><?php _e('Country/State', 'jigoshop'); ?></th>
						<th><?php _e("Rate (%)", 'jigoshop'); ?></th>
						<th><?php _e('Apply to shipping', 'jigoshop'); ?></th>
						<th><?php _e('Compound', 'jigoshop'); ?></th>
					</tr>
				</tfoot>
				<tbody>
					<?php
					$i = -1;
					if ( ! empty( $tax_rates ) ) :
						foreach ( $tax_rates as $rate ) :
							if ( $rate['is_all_states'] ) :
								if ( in_array( get_all_states_key( $rate ), $applied_all_states )) :
									continue;
								endif;
							endif;
	
							$i++; // increment counter after check for all states having been applied
	
							echo '<tr><td><a href="#" class="remove button">&times;</a></td>';
	
							echo '<td><select name="tax_classes[' . esc_attr( $i ) . ']"><option value="*">' . __('Standard Rate', 'jigoshop') . '</option>';
							if ( $tax_classes ) {
								foreach ( $tax_classes as $class ) :
									echo '<option value="' . sanitize_title( $class ) . '"';
	
									if ( $rate['class'] == sanitize_title( $class )) echo 'selected="selected"';
	
									echo '>' . $class . '</option>';
								endforeach;
							}
							echo '</select></td>';
							
							echo '<td><input type="text" value="' . esc_attr( $rate['label']  ) . '" name="tax_label[' . esc_attr( $i ) . ']" placeholder="' . __('Online Label', 'jigoshop') . '" size="10" /></td>';
	
							echo '<td><select name="tax_country[' . esc_attr( $i ) . ']" style="width:125px;">';
							if ( $rate['is_all_states'] ) :
								if ( is_array( $applied_all_states ) && !in_array( get_all_states_key( $rate ), $applied_all_states )) :
									$applied_all_states[] = get_all_states_key( $rate );
									jigoshop_countries::country_dropdown_options( $rate['country'], '*' ); //all-states
								else :
									continue;
								endif;
							else :
								jigoshop_countries::country_dropdown_options( $rate['country'], $rate['state'] );
							endif;
							echo '</select></td>';
							
							echo '<td><input type="text" value="' . esc_attr( $rate['rate']  ) . '" name="tax_rate[' . esc_attr( $i ) . ']" placeholder="' . __('Rate (%)', 'jigoshop') . '" size="6" /></td>';
							
							echo '<td><input type="checkbox" name="tax_shipping[' . esc_attr( $i ) . ']" ';
							if ( isset( $rate['shipping'] ) && $rate['shipping'] == 'yes' ) echo 'checked="checked"';
							echo ' /></td>';
							
							echo '<td><input type="checkbox" name="tax_compound[' . esc_attr( $i ) . ']" ';
	
							if ( isset( $rate['compound'] ) && $rate['compound'] == 'yes' ) echo 'checked="checked"';
							echo ' /></td></tr>';
							
						endforeach;
					endif;
					?>
				</tbody>
				
			</table>
			<div><a href="#" class="add button"><?php _e('+ Add Tax Rule', 'jigoshop'); ?></a></div>
		</div>
			
		<script type="text/javascript">
		/* <![CDATA[ */
			jQuery(function() {
				jQuery('#jigoshop_tax_rates a.add').live('click', function() {
					var size = jQuery('.tax_rate_rules tbody tr').size();
					jQuery('<tr> \
							<td><a href="#" class="remove button">&times;</a></td> \
							<td><select name="tax_classes[' + size + ']"> \
								<option value="*"><?php _e('Standard Rate', 'jigoshop'); ?></option> \
								<?php $tax_classes = $_tax->get_tax_classes(); if ( $tax_classes ) : foreach ( $tax_classes as $class ) : echo '<option value="' . sanitize_title($class) . '">' . $class . '</option>'; endforeach; endif; ?> \
								</select></td> \
							<td><input type="text" name="tax_label[' + size + ']" placeholder="<?php _e('Online Label', 'jigoshop'); ?>" size="10" /></td> \
							<td><select name="tax_country[' + size + ']" style="width:125px;"> \
									<?php jigoshop_countries::country_dropdown_options('', '', true); ?></select></td> \
							<td><input type="text" name="tax_rate[' + size + ']" placeholder="<?php _e('Rate (%)', 'jigoshop'); ?>" size="6" /> \
							<td><input type="checkbox" name="tax_shipping[' + size + ']" /></td> \
							<td><input type="checkbox" name="tax_compound[' + size + ']" /></td> \
							</tr>'
					).appendTo('#jigoshop_tax_rates .tax_rate_rules tbody');
					return false;
				});
				jQuery('#jigoshop_tax_rates a.remove').live('click', function(){
					var answer = confirm("<?php _e('Delete this rule?', 'jigoshop'); ?>");
					if (answer) jQuery(this).parent().parent().remove();
					return false;
				});
			});
			/* ]]> */
			</script>
		<?php
		
		$output = ob_get_contents();
		ob_end_clean();
		
		return $output;
	}
	
}

?>