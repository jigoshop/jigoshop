<?php
/**
 * Jigoshop_Admin_Settings class for management and display all Jigoshop option settings
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
	private static $page_name;
	
	
	/**
	 * Constructor
	 *
	 * @since 1.2
	 */
	protected function __construct() {
		
		self::$page_name = 'jigoshop_options';	// should match our WordPress Options table entry name
		
		$this->our_parser = new Jigoshop_Options_Parser( 
			Jigoshop_Options::instance()->get_default_options(), 
			$this->get_options_name()
		);

		add_action( 'admin_menu', array( &$this, 'add_settings_page' ) );
		add_action( 'admin_init', array( &$this, 'register_settings' ) );

	}
	
	
	/**
	 * Get the name of our Settings page and WordPress options table entry
	 *
	 * @since 1.2
	 */
	public function get_options_name() {
		return self::$page_name;
	}
	
	
	/**
	 * Add options page
	 *
	 * @since 1.2
	 */
	public function add_settings_page() {

		$admin_page = add_submenu_page( 'jigoshop', __( 'Jigoshop Settings' ), __( 'Jigoshop Settings' ), 'manage_options', $this->get_options_name(), array( &$this, 'output_markup' ) );

		add_action( 'admin_print_scripts-' . $admin_page, array( &$this, 'scripts' ) );
		add_action( 'admin_print_styles-' . $admin_page, array( &$this, 'styles' ) );

	}
	
	
	/**
	* Register settings
	*
	* @since 1.2
	*/
	public function register_settings() {

		register_setting( $this->get_options_name(), $this->get_options_name(), array ( &$this, 'validate_settings' ) );
		
		$slug = $this->get_current_tab_slug();
		$options = $this->our_parser->sections[$slug];
		
		add_settings_section( $slug, '', array( &$this, 'display_section' ), $this->get_options_name() );
			
		foreach ( $options as $option ) {
			$this->create_setting( $option );
		}

	}
	
	
	/**
	 * Create settings field
	 *
	 * @since 1.2
	 */
	public function create_setting( $args = array() ) {
	
		$defaults = array(
			'section'		=> '',
			'id'			=> null,
			'type'			=> '',
			'name'			=> __( '' ),
			'desc'			=> __( '' ),
			'tip'			=> '',
			'std'			=> '',
			'choices'		=> array(),
			'class'			=> '',
			'css'			=> '',
			'args_input'	=> ''
		);

		extract( wp_parse_args( $args, $defaults ) );

		$field_args = array(
			'type'			=> $type,
			'id'			=> $id,
			'name'			=> $name,
			'desc'			=> $desc,
			'tip'			=> $tip,
			"std"			=> $std,
			'choices'		=> $choices,
			'label_for'		=> $id,
			'class'			=> $class,
			'css'			=> $css,
			'args_input'	=> $args_input
		);
		
//		add_settings_field( $id, esc_attr( $name ), array( $this, 'display_setting' ), $this->get_options_name(), $section, $field_args );

		if ( $type <> 'heading' && $type <> 'title' ) {
			add_settings_field( 
				$id, 
				esc_attr( $name ), 
				array( &$this, 'display_option' ), 
				$this->get_options_name(), 
				$section, 
				$field_args
			);
		}
	}
	
	
	/**
	* jQuery Tabs
	*
	* @since 1.2
	*/
	public function scripts() {

		wp_print_scripts( 'jquery-ui-tabs' );

	}
	
	
	/**
	* Styling for the options page
	*
	* @since 1.2
	*/
	public function styles() {

		wp_register_style('jigoshop_settings_api_styles', jigoshop::assets_url() . '/assets/css/settings.css');
		wp_enqueue_style( 'jigoshop_settings_api_styles' );

	}
	
	
	/**
	 * Render the Options page
	 *
	 * @since 1.2
	 */
	public function output_markup() {
		?>
		<div class="wrap">
			<div class="icon32" id="icon-options-general"></div>
			<h2><?php _e( 'Jigoshop Settings' ) ?></h2>
			
			<?php
				if ( isset( $_GET['settings-updated'] ) && $_GET['settings-updated'] == true )
					echo '<div class="updated fade"><p>' . __( 'Jigoshop settings updated.' ) . '</p></div>';
			?>

			<div class="ui-tabs">
				<ul class="ui-tabs-nav">
					<?php echo $this->build_tab_menu_items(); ?>
				</ul>
				<form action="options.php" method="post" style="clear:both;">
					<?php settings_fields( $this->get_options_name() ); ?>
					<?php do_settings_sections( $this->get_options_name() ); ?>
					<p class="submit"><input name="Submit" type="submit" class="button-primary" value="<?php _e( 'Save Changes' ); ?>" /></p>
				</form>
			</div>
		</div>
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
		foreach ( $this->our_parser->tab_headers as $section ) {
			$this_slug = sanitize_title( $section );
			if ( $slug == $this_slug ) {
				$menus_li .= '<li><a
					class="current"
					title="'.$section.'"
					href="?page='.Jigoshop_Admin_Settings::get_options_name().'&tab='.$this_slug.'">' . $section . '</a></li>';
			} else {
				$menus_li .= '<li><a
					title="'.$section.'"
					href="?page='.Jigoshop_Admin_Settings::get_options_name().'&tab='.$this_slug.'">' . $section . '</a></li>';
			}
		}
		return $menus_li;
	}
	
	
	/**
	 * Format markup for an option and output it
	 *
	 * @since 1.2
	 */
	public function display_option( $item ) {
		echo $this->our_parser->format_item_for_display( $item );
	}
	
	
	/**
	 * Description for section
	 *
	 * @since 1.2
	 */
	public function display_section() {
		// code
	}
	
	
	/**
	 * Description for About section
	 *
	 * @since 1.2
	 */
	public function display_about_section() {

		// This displays on the "About" tab. Echo regular HTML here, like so:
		// echo '<p>Copyright 2011 me@example.com</p>';

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
				if ( $option['type'] == 'heading' ) {
					if ( $option['section'] == $_GET['tab'] ) {
						$current = $option['name'];
						break;
					}
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
		$current_options = Jigoshop_options::get_current_options();
//		$current_options['validation-error'] = true; // if no errors in validation, we will reset this to false
//		$current_options['message'] = "There was an error validating the data. No update occured!";
		$valid_input = $current_options;	// we start with the current options, plus the error flag and message
		$operation = "";
		
		if ( ! empty( $input )) foreach ( $input as $id => $value ) {
			$valid_input[$id] = $value;	// obviously we aren't validating very much yet (JAP)
		}
		return $valid_input;
	}
	
}


class Jigoshop_Options_Parser {

	var $these_options;		// The array of default options items to parse
	var $tab_headers;
	var $sections;


	function __construct( $option_items, $this_options_entry ) {
		$this->these_options = $option_items;
		$this->topf_parser();
	}


	private function topf_parser() {
		
		$tab_headers = array();
		$sections = array();
		
		foreach ( $this->these_options as $item ) {
			
			$defaults = array(
				'section'		=> '',
				'id'			=> null,
				'type'			=> '',
				'name'			=> __( '' ),
				'desc'			=> __( '' ),
				'tip'			=> '',
				'std'			=> '',
				'choices'		=> array(),
				'class'			=> '',
				'css'			=> '',
				'args_input'	=> ''
			);
	
			$item = wp_parse_args( $item, $defaults );
			
			if ( isset( $item['id'] ) ) $item['id'] = sanitize_title( $item['id']);
			
			if ( $item['type'] == "heading" ) {
				$tab_headers[] = $item['name'];
				$section_name = sanitize_title( $item['name'] );
			}
						
			$item['section'] = $section_name;
			$sections[$section_name][] = $item; // store each option item in it's section heading
			
		}

		$this->tab_headers = $tab_headers;
		$this->sections = $sections;
	}
	
	
	public function format_item_for_display( $item ) {
	
		$data = Jigoshop_Options::get_current_options();
		$display = "";
		$class = "";
		
		if ( isset( $item['class'] ) ) {
			$class = $item['class'];
		}
		
		$display .= '<div class="jigoshop-option jigoshop-option-'.$item['type'].'">'."\n";
		$display .= '<div class="jigoshop-controls '.$class.'">'."\n";

		switch ( $item['type'] ) {
			case 'tax_rates' :
				
				break;
				
			case 'coupons' :
				
				break;
				
			case 'gateway_options' :
				
				break;
				
			case 'shipping_options' :
				
				break;
				
			case 'single_select_page' :
				$page_setting = (int) $item['id'];

				$args = array(
					'name' => Jigoshop_Admin_Settings::get_options_name() . '[' . $item['id'] . ']',
					'id' => $item['id'],
					'sort_order' => 'ASC',
					'selected' => $page_setting
				);

				if (isset($args_input)) $args = wp_parse_args($args_input, $args);

				wp_dropdown_pages($args);

				break;

			case 'single_select_country' :
				$countries = jigoshop_countries::$countries;
				$country_setting = (string) $data[$item['id']];
				if (strstr($country_setting, ':')) :
					$country = current(explode(':', $country_setting));
					$state = end(explode(':', $country_setting));
				else :
					$country = $country_setting;
					$state = '*';
				endif;
				echo '<select class="select' . $class . '" name="' . Jigoshop_Admin_Settings::get_options_name() . '[' . $item['id'] . ']">';
				echo jigoshop_countries::country_dropdown_options($country, $state);
				echo '</select>';

				break;
				
			case 'multi_select_countries' :
				$countries = jigoshop_countries::$countries;
				asort($countries);
				$selections = (array) $data[$item['id']];
				echo '<div class="multi_select_countries"><ul>';
				if ($countries)
					foreach ($countries as $key => $val) :
						echo '<li><label><input type="checkbox" name="' . Jigoshop_Admin_Settings::get_options_name() . '[' . $item['id'] . '][] value="' . esc_attr( $key ) . '" ';
						echo in_array($key, $selections) ? 'checked="checked" />' : ' />';
						echo $val . '</label></li>';
					endforeach;
					echo '</ul></div>';
				break;
				
			case 'text':
				$display .= '<input
					id="'.$item['id'].'"
					class="jigoshop-input"
					name="'.Jigoshop_Admin_Settings::get_options_name().'['.$item['id'].']"
					type="'.$item['type'].'"
					value="'.$data[$item['id']].'" />'."\n";
				break;

			case 'textarea':
				$cols = '15';
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
					class="jigoshop-input"
					name="'.Jigoshop_Admin_Settings::get_options_name().'['.$item['id'].']"
					cols="'.$cols.'"
					rows="8">'.$ta_value.'</textarea>';
				break;

			case "radio":
				foreach ( $item['choices'] as $option => $name ) {
					$display .= '<input
						class="jigoshop-input jigoshop-radio"
						name="'.Jigoshop_Admin_Settings::get_options_name().'['.$item['id'].']"
						type="radio"
						value="'.$option.'" '.checked( $data[$item['id']], $option, '0' ).' /><label>'.$name.'</label><div style="clear:right;">  </div>';
				}
				break;

			case 'checkbox':
				$display .= '<input
					id="'.$item['id'].'"
					type="checkbox"
					class="jigoshop-input jigoshop-checkbox"
					name="'.Jigoshop_Admin_Settings::get_options_name().'['.$item['id'].']"
					'.checked( $data[$item['id']], true, false ).' />';
				break;

			case 'multicheck':
				$multi_stored = $data[$item['id']];
				foreach ( $item['choices'] as $key => $option ) {
					$display .= '<input
						id="'.$item['id'].'_'.$key.'"
						class="jigoshop-input jigoshop-checkbox"
						name="'.Jigoshop_Admin_Settings::get_options_name().'['.$item['id'].']['.$key.']"
						type="checkbox"
						'.checked( $multi_stored[$key], true, false ).' />
						<label for="'.$item['id'].'_'.$key.'">'.$option.'</label>';
				}
				break;

			case 'range':
				$display .= '<input
					id="'.$item['id'].'"
					class="jigoshop-input"
					name="'.Jigoshop_Admin_Settings::get_options_name().'['.$item['id'].']"
					type="'.$item['type'].'"
					min="'.$item['choices']['min'].'"
					max="'.$item['choices']['max'].'"
					step="'.$item['choices']['step'].'"
					value="'.$data[$item['id']].'" />'."\n";
				break;

			case 'select':
				$display .= '<select
					id="'.$item['id'].'"
					class="jigoshop-select"
					name="'.Jigoshop_Admin_Settings::get_options_name().'['.$item['id'].']" >'."\n";
				foreach ( $item['choices'] as $value => $label ) {
					$display .= '<option
						value="'.$value.'" '.selected( $data[$item['id']], $value, false ).' />'.$label.'
						</option>';
				}
				$display .= '</select>'."\n";
				break;
			
			default:
//				logme( "UNKOWN _type_ in parsing" );
				logme( $item );
		}

		if ( $item['type'] != 'heading' ) {
			if ( !isset( $item['desc'] ) ) {
				$explain_value = '';
			} else {
				$explain_value = $item['desc'];
			}
			$display .= '</div><div class="jigoshop-explain">' . $explain_value . '</div>' . "\n";
			$display .= '<div class="clear"> </div></div>' . "\n";
		}

		return $display;
	}

}

?>