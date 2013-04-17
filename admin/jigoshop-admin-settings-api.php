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
 * @package     Jigoshop
 * @category    Admin
 * @author      Jigoshop
 * @copyright   Copyright © 2011-2013 Jigoshop.
 * @license     http://jigoshop.com/license/commercial-edition
 */

class Jigoshop_Admin_Settings extends Jigoshop_Singleton {

	private $our_parser;

	/**
	 * Constructor
	 *
	 * @since 1.3
	 */
	protected function __construct() {
		
		$this->our_parser = new Jigoshop_Options_Parser(
			self::get_options()->get_default_options(),
			JIGOSHOP_OPTIONS
		);

		add_action( 'current_screen', array( $this, 'register_settings' ) );

	}

	/**
	 * Scripts for the Options page
	 *
	 * @since 1.3
	 */
	public function settings_scripts() {

    	// http://jquerytools.org/documentation/rangeinput/index.html
    	wp_register_script( 'jquery-tools', jigoshop::assets_url() . '/assets/js/jquery.tools.min.js', array( 'jquery' ), '1.2.7' );
    	wp_enqueue_script( 'jquery-tools' );

    	wp_register_script( 'jigoshop-bootstrap-tooltip', jigoshop::assets_url() . '/assets/js/bootstrap-tooltip.min.js', array( 'jquery' ), '2.0.3' );
    	wp_enqueue_script( 'jigoshop-bootstrap-tooltip' );

    	wp_register_script( 'jigoshop-select2', jigoshop::assets_url() . '/assets/js/select2.min.js', array( 'jquery' ), '3.1' );
    	wp_enqueue_script( 'jigoshop-select2' );

	}


	/**
	 * Styling for the options page
	 *
	 * @since 1.3
	 */
	public function settings_styles() {

		wp_register_style( 'jigoshop-select2', jigoshop::assets_url() . '/assets/css/select2.css', '', '3.1', 'screen' );
		wp_enqueue_style( 'jigoshop-select2' );

		do_action( 'jigoshop_settings_styles' );	// user defined stylesheets should be registered and queued

	}


	/**
	 * Register settings
	 *
	 * @since 1.3
	 */
	public function register_settings() {
		
		// Weed out all admin pages except the Jigoshop Settings page hits
		global $pagenow;
		if ( $pagenow <> 'admin.php' && $pagenow <> 'options.php' ) return;
		$screen = get_current_screen();
		if ( $screen->base <> 'jigoshop_page_jigoshop_settings' && $screen->base <> 'options' ) return;
		
		$slug = $this->get_current_tab_slug();
		$options = isset( $this->our_parser->tabs[$slug] ) ? $this->our_parser->tabs[$slug] : '';

		if ( ! is_array( $options ) ) {
			jigoshop_log( "Jigoshop Settings API: -NO- valid options for 'register_settings()' - EXITING with:" );
			jigoshop_log( $slug );
			return;
		}

		register_setting( JIGOSHOP_OPTIONS, JIGOSHOP_OPTIONS, array( $this, 'validate_settings' ) );

		if ( is_array( $options )) foreach ( $options as $index => $option ) {
			switch ( $option['type'] ) {
			case 'title':
				add_settings_section( $option['section'], $option['name'], array( $this, 'display_section' ), JIGOSHOP_OPTIONS );
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
	 * @since 1.3
	 */
	public function create_setting( $index, $option = array() ) {

		$defaults = array(
			'tab'			=> '',
			'section'		=> '',
			'id'			=> null,
			'type'			=> '',
			'name'			=> '',
			'desc'			=> '',
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

		if ( $type <> 'tab' ) {
			add_settings_field(
				$id,
				($type == 'checkbox') ? '' : esc_attr( $name ),
				array( $this, 'display_option' ),
				JIGOSHOP_OPTIONS,
				$section,
				$field_args
			);
		}
	}


	/**
	 * Format markup for an option and output it
	 *
	 * @since 1.3
	 */
	public function display_option( $option ) {
		echo $this->our_parser->format_option_for_display( $option );
	}


	/**
	 * Description for section
	 *
	 * @since 1.3
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
	 * @since 1.3
	 */
	public function output_markup() {
		?>
			<div class="wrap jigoshop">

				<div class="icon32 icon32-jigoshop-settings" id="icon-jigoshop"><br></div>
				<?php do_action( 'jigoshop_admin_settings_notices' ); ?>
				<h2 class="nav-tab-wrapper jigoshop-nav-tab-wrapper">
					<?php echo $this->build_tab_menu_items(); ?>
				</h2>

				<noscript>
					<div id="jigoshop-js-warning" class="error"><?php _e( 'Warning- This options panel may not work properly without javascript!', 'jigoshop' ); ?></div>
				</noscript>

				<?php settings_errors(); ?>

				<form action="options.php" id="mainform" method="post">

					<div class="jigoshop-settings">
						<div id="tabs-wrap">

							<?php settings_fields( JIGOSHOP_OPTIONS ); ?>
							<?php do_settings_sections( JIGOSHOP_OPTIONS ); ?>

							<p class="submit">
								<input name="Submit" type="submit" class="button-primary" value="<?php echo sprintf( __( "Save %s Changes", 'jigoshop' ), $this->get_current_tab_name() ); ?>" />
							</p>

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
	 * @since 1.3
	 */
	function build_tab_menu_items() {
		$menus_li = '';
		$slug = $this->get_current_tab_slug();
		foreach ( $this->our_parser->tab_headers as $tab ) {
			$this_slug = sanitize_title( $tab );
			if ( $slug == $this_slug ) {
				$menus_li .= '<a class="nav-tab nav-tab-active"
					title="'.$tab.'"
					href="?page=jigoshop_settings&tab='.$this_slug.'">' . $tab . '</a>';
			} else {
				$menus_li .= '<a class="nav-tab"
					title="'.$tab.'"
					href="?page=jigoshop_settings&tab='.$this_slug.'">' . $tab . '</a>';
			}
		}
		return $menus_li;
	}


	/**
	 * Return the current Tab slug in view
	 *
	 * @since 1.3
	 */
	public function get_current_tab_slug() {
		$current = "";

		if ( isset( $_GET['tab'] ) ) {
			$current = $_GET['tab'];
		} else if ( isset( $_POST['_wp_http_referer'] ) && strpos($_POST['_wp_http_referer'], '&tab=') !== false ) {
			// /site/wp-admin/admin.php?page=jigoshop_settings&tab=products-inventory&settings-updated=true
			// find the 'tab'
			$result = strstr( $_POST['_wp_http_referer'], '&tab=' );
			// &tab=products-inventory&settings-updated=true
			$result = substr( $result, 5 );
			// products-inventory&settings-updated=true
			$end_pos = strpos( $result, '&' );
			$current = substr( $result, 0 , $end_pos !== false ? $end_pos : strlen( $result ) );
			// products-inventory
		} else {
			$current = $this->our_parser->these_options[0]['name'];
		}
		return sanitize_title( $current );
	}


	/**
	 * Return the current Tab full name in view
	 *
	 * @since 1.3
	 */
	public function get_current_tab_name() {

		$current = $this->our_parser->these_options[0]['name'];

		$slug = $this->get_current_tab_slug();
		foreach ( $this->our_parser->tab_headers as $tab ) {
			$this_slug = sanitize_title( $tab );
			if ( $slug == $this_slug ) {
				$current = $tab;
			}
		}
		return $current;
	}


	/**
	 * Validate settings
	 *
	 * @since 1.3
	 */
	public function validate_settings( $input ) {

		if ( empty( $_POST ) ) {
			return $input;
		}

		$defaults = $this->our_parser->these_options;
		$current_options = self::get_options()->get_current_options();

		$valid_input = $current_options;			// we start with the current options

		// Find the current TAB we are working with and use it's option settings
		$this_section = $this->get_current_tab_name();
		$tab = $this->our_parser->tabs[sanitize_title( $this_section )];

		// with each option, get it's type and validate it
		if ( ! empty( $tab )) foreach ( $tab as $index => $setting ) {
			if ( isset( $setting['id'] ) ) {
			
				// special case tax classes should be updated, they will do nothing if this is not the right TAB
				if ( $setting['id'] == 'jigoshop_tax_rates' ) {
					$valid_input['jigoshop_tax_rates'] = $this->get_updated_tax_classes();
					update_option( $setting['id'], $valid_input['jigoshop_tax_rates'] ); // TODO: remove in v1.5 - provides compatibility
					continue;
				}
				
				// get this settings options
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
							$result = call_user_func( $option['update'] );
							$valid_input[$setting['id']] = $result;
							update_option( $setting['id'], $valid_input[$setting['id']] ); // TODO: remove in v1.5 - provides compatibility
						}
					}
					break;

				case 'multi_select_countries' :
					if ( isset( $value ) ) {
						$countries = jigoshop_countries::$countries;
						asort( $countries );
						$selected = array();
						foreach ( $countries as $key => $val ) {
							if ( in_array( $key, (array)$value ) ) {
								$selected[] = $key;
							}
						}
						$valid_input[$setting['id']] = $selected;
						update_option( $setting['id'], $valid_input[$setting['id']] ); // TODO: remove in v1.5 - provides compatibility
					}
					break;

				case 'checkbox' :
					// there will be no $value for a false checkbox, set it now
					$valid_input[$setting['id']] = isset( $value ) ? 'yes' : 'no';
					update_option( $setting['id'], $valid_input[$setting['id']] ); // TODO: remove in v1.5 - provides compatibility
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
					update_option( $setting['id'], $valid_input[$setting['id']] ); // TODO: remove in v1.5 - provides compatibility
					break;

				case 'text' :
				case 'longtext' :
				case 'textarea' :
					$valid_input[$setting['id']] = esc_attr( jigowatt_clean( $value ) );
					update_option( $setting['id'], $valid_input[$setting['id']] ); // TODO: remove in v1.5 - provides compatibility
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
					} else {
						$valid_input[$setting['id']] = esc_attr( jigowatt_clean( $email ) );
					}
					update_option( $setting['id'], $valid_input[$setting['id']] ); // TODO: remove in v1.5 - provides compatibility
					break;

				case 'decimal' :
					$cleaned = jigowatt_clean( $value );
					if ( ! jigoshop_validation::is_decimal( $cleaned ) && $cleaned <> '' ) {
						add_settings_error(
							$setting['id'],
							'jigoshop_decimal_error',
							sprintf(__('You entered "%s" as the value for "%s" in "%s" and it was not a valid decimal number (may have leading negative sign, with optional decimal point, numbers 0-9).  It was not saved and the original is still in use.','jigoshop'), $value, $setting['name'], $setting['section'] ),
							'error'
						);
						$valid_input[$setting['id']] = $current_options[$setting['id']];
					} else {
						$valid_input[$setting['id']] = $cleaned;
					}
					update_option( $setting['id'], $valid_input[$setting['id']] ); // TODO: remove in v1.5 - provides compatibility
					break;

				case 'integer' :
					$cleaned = jigowatt_clean( $value );
					if ( ! jigoshop_validation::is_integer( $cleaned ) && $cleaned <> '' ) {
						add_settings_error(
							$setting['id'],
							'jigoshop_integer_error',
							sprintf(__('You entered "%s" as the value for "%s" in "%s" and it was not a valid integer number (may have leading negative sign, numbers 0-9).  It was not saved and the original is still in use.','jigoshop'), $value, $setting['name'], $setting['section'] ),
							'error'
						);
						$valid_input[$setting['id']] = $current_options[$setting['id']];
					} else {
						$valid_input[$setting['id']] = $cleaned;
					}
					update_option( $setting['id'], $valid_input[$setting['id']] ); // TODO: remove in v1.5 - provides compatibility
					break;

				case 'natural' :
					$cleaned = jigowatt_clean( $value );
					if ( ! jigoshop_validation::is_natural( $cleaned ) && $cleaned <> '' ) {
						add_settings_error(
							$setting['id'],
							'jigoshop_natural_error',
							sprintf(__('You entered "%s" as the value for "%s" in "%s" and it was not a valid natural number (numbers 0-9).  It was not saved and the original is still in use.','jigoshop'), $value, $setting['name'], $setting['section'] ),
							'error'
						);
						$valid_input[$setting['id']] = $current_options[$setting['id']];
					} else {
						$valid_input[$setting['id']] = $cleaned;
					}
					update_option( $setting['id'], $valid_input[$setting['id']] ); // TODO: remove in v1.5 - provides compatibility
					break;

				default :
					if ( isset( $value ) ) {
						$valid_input[$setting['id']] = $value;
						update_option( $setting['id'], $valid_input[$setting['id']] ); // TODO: remove in v1.5 - provides compatibility
					}
					break;
				}
			}
		}


        // remove all jigoshop_update_options actions on shipping classes when not on the shipping tab
        if ( $this_section != __('Shipping','jigoshop') ) {
            $this->remove_update_options( jigoshop_shipping::get_all_methods() );
        }

        if ( $this_section != __('Payment Gateways','jigoshop') ) {
            $this->remove_update_options( jigoshop_payment_gateways::payment_gateways() );
        }

		// Allow any hooked in option updating
		do_action( 'jigoshop_update_options' );

		$errors = get_settings_errors();
		if ( empty( $errors ) ) {
			add_settings_error(
				'',
				'settings_updated',
				sprintf(__('"%s" settings were updated successfully.','jigoshop'), $this_section ),
				'updated'
			);
		}

		return $valid_input;	// send it back to WordPress for saving

	}

	/**
	 * Remove all jigoshop_update_options actions on shipping and payment classes when not on those tabs
	 *
	 * @since 	1.3
	 */
	private function remove_update_options( $classes ) {

        if ( empty( $classes )) return;

        foreach ( $classes as $class ) :
            remove_action( 'jigoshop_update_options', array( $class, 'process_admin_options' ));
        endforeach;

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
	 * @since 	1.3
	 */
	function get_updated_tax_classes() {

		$taxFields = array(
			'tax_classes' => '',
			'tax_country' => '',
			'tax_rate'    => '',
			'tax_label'   => '',
			'tax_shipping'=> '',
			'tax_compound'=> ''
		);

		$tax_rates = array();

		/* Save each array key to a variable */
		foreach ( $taxFields as $name => $val )
			if ( isset( $_POST[$name] )) $taxFields[$name] = $_POST[$name];

		extract( $taxFields );

		for ( $i = 0; $i < sizeof( $tax_classes); $i++ ) :

			if ( empty( $tax_rate[$i] )) continue;

			$countries = $tax_country[$i];
			$label     = trim($tax_label[$i]);
			$rate      = number_format((float)jigowatt_clean($tax_rate[$i]), 4);
			$class     = jigowatt_clean($tax_classes[$i]);

			/* Checkboxes */
			$shipping = !empty($tax_shipping[$i]) ? 'yes' : 'no';
			$compound = !empty($tax_compound[$i]) ? 'yes' : 'no';

			/* Save the state & country separately from options eg US:OH */
			$states  = array();
			foreach ( $countries as $k => $countryCode ) :
				if ( strstr($countryCode, ':')) :
					$cr = explode(':', $countryCode);
					$states[$cr[1]]  = $cr[0];
					unset($countries[$k]);
				endif;
			endforeach;

			/* Save individual state taxes, eg OH => US (State => Country) */
			foreach ( $states as $state => $country ) :
				$tax_rates[] = array(
					'country'      => $country,
					'label'        => $label,
					'state'        => $state,
					'rate'         => $rate,
					'shipping'     => $shipping,
					'class'        => $class,
					'compound'     => $compound,
					'is_all_states'=> false //determines if admin panel should show 'all_states'
				);
			endforeach;

			foreach ( $countries as $country ) :

				/* Countries with states */
				if ( jigoshop_countries::country_has_states( $country )) {

					foreach ( array_keys( jigoshop_countries::$states[$country] ) as $state ) :
						$tax_rates[] = array(
							'country'      => $country,
							'label'        => $label,
							'state'        => $state,
							'rate'         => $rate,
							'shipping'     => $shipping,
							'class'        => $class,
							'compound'     => $compound,
							'is_all_states'=> true
						);
					endforeach;

				} else {  /* This country has no states, eg AF */

					 $tax_rates[] = array(
						'country'      => $country,
						'label'        => $label,
						'state'        => '*',
						'rate'         => $rate,
						'shipping'     => $shipping,
						'class'        => $class,
						'compound'     => $compound,
						'is_all_states'=> false
					);

				}

			endforeach;

		endfor;

		usort( $tax_rates, array( $this, 'csort_tax_rates' ) );

		return $tax_rates;

	}

}


/**
 * Options Parser Class
 *
 * Used by the Jigoshop_Admin_Settings class to parse the Jigoshop_Options into sections
 * Provides formatted output for display of all Option types
 *
 * @since 	1.3
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
				'name'			=> '',
				'desc'			=> '',
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

			if ( $item['type'] == 'tab' ) {
				$tab_name = sanitize_title( $item['name'] );
				$tab_headers[$tab_name] = $item['name'];    // used by get_current_tab_name()
				continue;
			}

			if ( $item['type'] == 'title' ) {
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

		$data = Jigoshop_Base::get_options()->get_current_options();

		if ( ! isset( $item['id'] )) return '';         // ensure we have an id to work with
		
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
		case 'user_defined':
			if ( isset( $item['display'] ) ) {
				if ( is_callable( $item['display'], true ) ) {
					$display .= call_user_func( $item['display'] );
				}
			}
			break;

		case 'gateway_options':
			foreach ( jigoshop_payment_gateways::payment_gateways() as $gateway ) :
				$gateway->admin_options();
			endforeach;
			break;

		case 'shipping_options':
			foreach ( jigoshop_shipping::get_all_methods() as $shipping_method ) :
				$shipping_method->admin_options();
			endforeach;
			break;

		case 'tax_rates':
			$display .= $this->format_tax_rates_for_display( $item );
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
		case 'single_select_page':
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
			$id = $item['id'];
			$display = $parts[0] . '<select id="'.$id.'" class="'.$class.'"' . $parts[1];
			?>
				<script type="text/javascript">
				/*<![CDATA[*/
					jQuery(function() {
						jQuery("#<?php echo $id; ?>").select2({ width: '250px' });
					});
				/*]]>*/
				</script>
			<?php
			break;

		case 'single_select_country':
			$countries = jigoshop_countries::$countries;
			$country_setting = (string) $data[$item['id']];
			if ( strstr( $country_setting, ':' )) :
				$country = current( explode( ':', $country_setting) );
				$state = end( explode( ':', $country_setting) );
			else :
				$country = $country_setting;
				$state = '*';
			endif;
			$id = $item['id'];
			$display .= '<select id="'.$id.'" class="single_select_country '.$class.'" name="' . JIGOSHOP_OPTIONS . '[' . $item['id'] . ']">';
			$display .= jigoshop_countries::country_dropdown_options($country, $state, true, false, false);
			$display .= '</select>';
			?>
				<script type="text/javascript">
				/*<![CDATA[*/
					jQuery(function() {
						jQuery("#<?php echo $id; ?>").select2({ width: '500px' });
					});
				/*]]>*/
				</script>
			<?php
			break;

		case 'multi_select_countries':
			$countries = jigoshop_countries::$countries;
			asort( $countries );
			$selections = (array) $data[$item['id']];

			$display .= '<select multiple="multiple"
				id="'.$item['id'].'"
				class="jigoshop-input jigoshop-select '.$class.'"
				name="'.JIGOSHOP_OPTIONS.'['.$item['id'].'][]" >';
			foreach ( $countries as $key => $val ) {
				$display .= '<option value="'.esc_attr( $key ).'" '.(in_array( $key, $selections ) ? 'selected="selected"' : '').' />'.$val.'</option>';
			}
			$display .= '</select>';
			$id = $item['id'];
			?>
				<script type="text/javascript">
				/*<![CDATA[*/
					jQuery(function() {
						jQuery("#<?php echo $id; ?>").select2({ width: '500px' });
					});
				/*]]>*/
				</script>
			<?php
			break;

		case 'button':
			if ( isset( $item['extra'] ) )
				$display .= '<a  id="'.$item['id'].'"
					class="button '.$class.'"
					href="'. esc_attr( $item['extra'] ) .'"
					>'. esc_attr( $item['desc'] ) .'</a>';
			$item['desc'] = '';     // temporarily remove it so it doesn't display twice
			break;

		case 'decimal':				// decimal numbers are positive or negative 0-9 inclusive, may include decimal
			$display .= '<input
				id="'.$item['id'].'"
				class="jigoshop-input jigoshop-text '.$class.'"
				name="'.JIGOSHOP_OPTIONS.'['.$item['id'].']"
				type="number"
				step="any"
				size="20"
				value="'. esc_attr( $data[$item['id']] ).'" />';
			break;

		case 'integer':				// integer numbers are positive or negative 0-9 inclusive
		case 'natural':				// natural numbers are positive 0-9 inclusive
			$display .= '<input
				id="'.$item['id'].'"
				class="jigoshop-input jigoshop-text '.$class.'"
				name="'.JIGOSHOP_OPTIONS.'['.$item['id'].']"
				type="number"
				size="20"
				value="'. esc_attr( $data[$item['id']] ).'" />';
			break;

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
						id="' . $item['id'] . '[' . $option . ']"
						type="radio"
						value="'.$option.'" '.checked( $data[$item['id']], $option, false ).' /><label for="' . $item['id'] . '[' . $option . ']">'.$name.'</label>';
				}
				$display .= '</div>';

			} else if ( isset( $item['extra'] ) && in_array( 'vertical', $item['extra'] ) ) {

				$display .= '<ul class="jigoshop-radio-vert">';
				foreach ( $item['choices'] as $option => $name ) {
					$display .= '<li><input
						class="jigoshop-input jigoshop-radio '.$class.'"
						name="'.JIGOSHOP_OPTIONS.'['.$item['id'].']"
						id="' . $item['id'] . '[' . $option . ']"
						type="radio"
						value="'.$option.'" '.checked( $data[$item['id']], $option, false ).' /><label for="' . $item['id'] . '[' . $option . ']">'.$name.'</label></li>';
				}
				$display .= '</ul>';

			}
			break;

		case 'checkbox':
			$display .= '<span class="jigoshop-container"><input
				id="'.$item['id'].'"
				type="checkbox"
				class="jigoshop-input jigoshop-checkbox '.$class.'"
				name="'.JIGOSHOP_OPTIONS.'['.$item['id'].']"
				'.checked($data[$item['id']], 'yes', false).' />
				<label for="'.$item['id'].'">'.$item['name'].'</label></span>';
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
				if ( is_array( $label )) {
					$display .= '<optgroup label="'.$value.'">';
					foreach ( $label as $subValue => $subLabel ) {
						$display .= '<option
							value="'.esc_attr( $subValue ).'" '.selected( $data[$item['id']], $subValue, false ).' />'.$subLabel.'
							</option>';
					}
					$display .= '</optgroup>';             
				} 
				else {
					$display .= '<option
						value="'.esc_attr( $value ).'" '.selected( $data[$item['id']], $value, false ).' />'.$label.'
						</option>';
				}
			}
			$display .= '</select>';
			$id = $item['id'];
			?>
				<script type="text/javascript">
				/*<![CDATA[*/
					jQuery(function() {
						jQuery("#<?php echo $id; ?>").select2({ width: '250px' });
					});
				/*]]>*/
				</script>
			<?php
			break;

		default:
			jigoshop_log( "UNKOWN _type_ in Options parsing" );
			jigoshop_log( $item );
		}
		
		if ( $item['type'] != 'tab' ) {
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


	function array_find( $needle, $haystack ) {
		foreach ( $haystack as $key => $val ):
			if ( $needle == array( "label" => $val['label'], "compound" => $val['compound'], 'rate' => $val['rate'], 'shipping' => $val['shipping'], 'class' => $val['class'] ) ):
				return $key;
			endif;
		endforeach;
		return false;
	}
	
	
	function array_compare( $tax_rates ) {
		$after = array();
		foreach ( $tax_rates as $key => $val ):
			$first_two = array("label" => $val['label'], "compound" => $val['compound'], 'rate' => $val['rate'], 'shipping' => $val['shipping'], 'class' => $val['class'] );
			$found = $this->array_find( $first_two, $after );
			if ( $found !== false ):
				$combined  = $after[$found]["state"];
				$combined2 = $after[$found]["country"];
				$combined = !is_array($combined) ? array($combined) : $combined;
				$combined2 = !is_array($combined2) ? array($combined2) : $combined2;
				$after[$found] = array_merge($first_two,array( "state" => array_merge($combined,array($val['state'])), "country" => array_merge($combined2,array($val['country'])) ));
			else:
				$after = array_merge($after,array(array_merge($first_two,array("state" => $val['state'], "country" => $val['country']))));
			endif;
		endforeach;
		return $after;
	}
	
	
	/*
	 *	Format tax rates array for display
	 */
	function format_tax_rates_for_display( $value ) {

		$_tax = new jigoshop_tax();
		$tax_classes = $_tax->get_tax_classes();
		$tax_rates = (array) Jigoshop_Base::get_options()->get_option( 'jigoshop_tax_rates' );
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
					if ( $tax_rates && is_array( $tax_rates ) && sizeof( $tax_rates ) > 0 ) :

						$tax_rates = $this->array_compare( $tax_rates );
						
						foreach ( $tax_rates as $rate ) :
							if ( isset($rate['is_all_states']) && in_array($tax_rate['country'].$tax_rate['class'], $applied_all_states) )
								continue;

							$i++;// increment counter after check for all states having been applied

							echo '<tr class="tax_rate"><td><a href="#" class="remove button">&times;</a></td>';

							echo '<td><select id="tax_classes[' . esc_attr( $i ) . ']" name="tax_classes[' . esc_attr( $i ) . ']"><option value="*">' . __('Standard Rate', 'jigoshop') . '</option>';
							if ( $tax_classes ) {
								foreach ( $tax_classes as $class ) :
									echo '<option value="' . sanitize_title( $class ) . '"';

									if ( isset($rate['class']) && $rate['class'] == sanitize_title( $class )) echo 'selected="selected"';

									echo '>' . $class . '</option>';
								endforeach;
							}
							echo '</select></td>';

							echo '<td><input type="text" value="' . esc_attr( $rate['label']  ) . '" name="tax_label[' . esc_attr( $i ) . ']" placeholder="' . __('Online Label', 'jigoshop') . '" size="10" /></td>';

							echo '<td><select name="tax_country[' . esc_attr( $i ) . '][]" id="tax_country_' . esc_attr( $i ) . '" class="tax_select2" multiple="multiple" style="width:220px;">';
							if ( isset($rate['is_all_states']) ) :
								if ( is_array( $applied_all_states ) && !in_array( $tax_rate['country'].$tax_rate['class'], $applied_all_states )) :
									$applied_all_states[] = $tax_rate['country'].$tax_rate['class'];
									jigoshop_countries::country_dropdown_options( $rate['country'], '*', true ); //all-states
								else :
									continue;
								endif;
							else :
								jigoshop_countries::country_dropdown_options( $rate['country'], $rate['state'], true );
							endif;
							echo '</select>';

							echo '<button class="select_none button">'.__('None', 'jigoshop').'</button><button class="button select_us_states">'.__('US States', 'jigoshop').'</button><button class="button select_europe">'.__('EU States', 'jigoshop').'</button></td>';
							echo '<td><input type="text" value="' . esc_attr( $rate['rate']  ) . '" name="tax_rate[' . esc_attr( $i ) . ']" placeholder="' . __('Rate (%)', 'jigoshop') . '" size="6" /></td>';

							echo '<td><input type="checkbox" name="tax_shipping[' . esc_attr( $i ) . ']" ';
							if ( isset( $rate['shipping'] ) && $rate['shipping'] == 'yes' ) echo 'checked="checked"';
							echo ' /></td>';

							echo '<td><input type="checkbox" name="tax_compound[' . esc_attr( $i ) . ']" ';

							if ( isset( $rate['compound'] ) && $rate['compound'] == 'yes' ) echo 'checked="checked"';
							echo ' /></td></tr>';
							?><script type="text/javascript">
							/*<![CDATA[*/
								jQuery(function() {
									jQuery("#tax_country_<?php echo esc_attr( $i ); ?>").select2();
								});
							/*]]>*/
							</script><?php
						endforeach;
					endif;
					?>
				</tbody>

			</table>
			<div><a href="#" class="add button"><?php _e('+ Add Tax Rule', 'jigoshop'); ?></a></div>
		</div>

		<script type="text/javascript">
		/*<![CDATA[*/
			jQuery(function() {

				jQuery(document.body).on('click', 'tr.tax_rate .select_none', function(){
					jQuery(this).closest('td').find('select option').removeAttr("selected");
					jQuery(this).closest('td').find('select.tax_select2').trigger("change");
					return false;
				});
				jQuery(document.body).on('click', 'tr.tax_rate .select_us_states', function(e){
					jQuery(this).closest('td').find('select optgroup[label="<?php _e( 'United States', 'jigoshop' ); ?>"] option').attr("selected","selected");
					jQuery(this).closest('td').find('select.tax_select2').trigger("change");
					return false;
				});
				jQuery(document.body).on('change', 'tr.tax_rate .options select', function(e){
					jQuery(this).trigger("liszt:updated");
					jQuery(this).closest('td').find('label').text( jQuery(":selected", this).length + ' ' + '<?php _e('countries/states selected', 'jigoshop'); ?>' );
				});
				jQuery(document.body).on('click', 'tr.tax_rate .select_europe', function(e){
					jQuery(this).closest('td').find('option[value="BE"],option[value="FR"],option[value="DE"],option[value="IT"],option[value="LU"],option[value="NL"],option[value="DK"],option[value="IE"],option[value="GR"],option[value="PT"],option[value="ES"],option[value="AT"],option[value="FI"],option[value="SE"],option[value="CY"],option[value="CZ"],option[value="EE"],option[value="HU"],option[value="LV"],option[value="LT"],option[value="MT"],option[value="PL"],option[value="SK"],option[value="SI"],option[value="RO"],option[value="BG"],option[value="IM"],option[value="GB"]').attr("selected","selected");
					jQuery(this).closest('td').find('select.tax_select2').trigger("change");
					return false;
				});

				jQuery(document.body).on('click', '#jigoshop_tax_rates a.add', function() {
					var size = jQuery('.tax_rate_rules tbody tr').size();
					jQuery('<tr> \
							<td><a href="#" class="remove button">&times;</a></td> \
							<td><select name="tax_classes[' + size + ']"> \
								<option value="*"><?php _e('Standard Rate', 'jigoshop'); ?></option> \
								<?php $tax_classes = $_tax->get_tax_classes(); if ( $tax_classes ) : foreach ( $tax_classes as $class ) : echo '<option value="' . sanitize_title($class) . '">' . $class . '</option>'; endforeach; endif; ?> \
								</select></td> \
							<td><input type="text" name="tax_label[' + size + ']" placeholder="<?php _e('Online Label', 'jigoshop'); ?>" size="10" /></td> \
							<td><select name="tax_country[' + size + '][]" id="tax_country_' + size +'" multiple="multiple" style="width:220px;"> \
									<?php jigoshop_countries::country_dropdown_options('', '', true); ?></select></td> \
							<td><input type="text" name="tax_rate[' + size + ']" placeholder="<?php _e('Rate (%)', 'jigoshop'); ?>" size="6" /> \
							<td><input type="checkbox" name="tax_shipping[' + size + ']" /></td> \
							<td><input type="checkbox" name="tax_compound[' + size + ']" /></td> \
							</tr>'
					).appendTo('#jigoshop_tax_rates .tax_rate_rules tbody');
					jQuery('#tax_country_' + size).select2();
					return false;
				});
				jQuery(document.body).on('click', '#jigoshop_tax_rates a.remove', function(){
					var answer = confirm("<?php _e('Delete this rule?', 'jigoshop'); ?>");
					if (answer) jQuery(this).parent().parent().remove();
					return false;
				});
			});
			/*]]>*/
			</script>
		<?php

		$output = ob_get_contents();
		ob_end_clean();

		return $output;
	}

}

?>